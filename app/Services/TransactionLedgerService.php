<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionLedgerService
{
    /**
     * Update an income or expense transaction and reconcile affected wallet balances atomically.
     *
     * @param  array{wallet_id: int, trip_id: int|null, category_id: int, type: string, amount: float|int, title: string, note: string|null, transaction_date: \Carbon\CarbonInterface}  $data
     */
    public function updateTransaction(User $user, int $transactionId, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $transactionId, $data) {
            $tx = Transaction::where('user_id', $user->id)
                ->lockForUpdate()
                ->findOrFail($transactionId);

            if ($tx->type === 'transfer') {
                throw ValidationException::withMessages([
                    'amount' => 'Transaksi transfer tabungan tidak dapat diubah tipe transaksinya.',
                ]);
            }

            $walletIds = collect([$tx->wallet_id, $data['wallet_id']])
                ->unique()
                ->sort()
                ->values();

            $wallets = Wallet::where('user_id', $user->id)
                ->whereIn('id', $walletIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $oldWallet = $wallets->get($tx->wallet_id);
            $newWallet = $wallets->get($data['wallet_id']);

            if (!$oldWallet || !$newWallet) {
                throw ValidationException::withMessages([
                    'selectedWalletId' => 'Dompet yang dipilih tidak valid.',
                ]);
            }

            $oldBalances = [
                $oldWallet->id => (float) $oldWallet->current_balance,
                $newWallet->id => (float) $newWallet->current_balance,
            ];

            // Reverse original transaction impact
            $oldBalances[$oldWallet->id] += $tx->type === 'income'
                ? -(float) $tx->amount
                : (float) $tx->amount;

            // Apply updated transaction impact
            $newAmount = (float) $data['amount'];
            $oldBalances[$newWallet->id] += $data['type'] === 'income'
                ? $newAmount
                : -$newAmount;

            foreach ($oldBalances as $balance) {
                if ($balance < 0) {
                    throw ValidationException::withMessages([
                        'amount' => 'Saldo dompet tidak mencukupi untuk perubahan transaksi ini.',
                    ]);
                }
            }

            foreach ($wallets as $wallet) {
                $wallet->current_balance = $oldBalances[$wallet->id];
                $wallet->save();
            }

            $tx->update($data);

            return $tx;
        });
    }

    /**
     * Delete a transaction owned by the user and reverse its balance impact atomically.
     */
    public function deleteTransaction(User $user, int $transactionId): void
    {
        DB::transaction(function () use ($user, $transactionId) {
            $tx = Transaction::where('user_id', $user->id)
                ->lockForUpdate()
                ->findOrFail($transactionId);

            $walletIds = collect([$tx->wallet_id, $tx->destination_wallet_id])
                ->filter()
                ->unique()
                ->sort()
                ->values();

            $wallets = Wallet::where('user_id', $user->id)
                ->whereIn('id', $walletIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $sourceWallet = $wallets->get($tx->wallet_id);
            $destWallet = $tx->destination_wallet_id ? $wallets->get($tx->destination_wallet_id) : null;
            $amount = (float) $tx->amount;

            if ($tx->type === 'income' && $sourceWallet) {
                if ((float) $sourceWallet->current_balance < $amount) {
                    throw ValidationException::withMessages([
                        'transaction' => 'Pemasukan tidak dapat dihapus karena saldo dompet sudah terpakai.',
                    ]);
                }
                $sourceWallet->decrement('current_balance', $amount);
            } elseif ($tx->type === 'expense' && $sourceWallet) {
                $sourceWallet->increment('current_balance', $amount);
            } elseif ($tx->type === 'transfer') {
                if ($destWallet) {
                    if ((float) $destWallet->current_balance < $amount) {
                        throw ValidationException::withMessages([
                            'transaction' => 'Transfer tidak dapat dibatalkan karena saldo dompet tujuan sudah terpakai.',
                        ]);
                    }
                    $destWallet->decrement('current_balance', $amount);
                }
                if ($sourceWallet) {
                    $sourceWallet->increment('current_balance', $amount);
                }
            }

            $tx->delete();
        });
    }
}
