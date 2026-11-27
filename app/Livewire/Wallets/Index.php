<?php

namespace App\Livewire\Wallets;

use App\Concerns\ParsesNominal;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use ParsesNominal;

    // Create / Edit wallet / target modal
    public bool $isCreateModalOpen = false;
    public ?int $editingWalletId = null;
    public string $name = '';
    public string $accountLabel = '';
    public string $type = 'trip'; // operational, trip, saving
    public mixed $initialBalance = 0;
    public mixed $targetAmount = null;
    public mixed $dailyLimit = null;

    // Deposit / Tambah Saldo Tabungan modal
    public bool $isDepositModalOpen = false;
    public ?int $depositWalletId = null;
    public mixed $depositAmount = 100000;
    public string $sourceMode = 'operational'; // 'operational' (sisihkan kas) or 'external' (dana masuk baru)
    public ?int $sourceWalletId = null;
    public string $depositNote = '';

    // Transfer / Cairkan Dana antar dompet modal
    public bool $isTransferModalOpen = false;
    public ?int $fromWalletId = null;
    public ?int $toWalletId = null;
    public mixed $transferAmount = 100000;
    public string $transferNote = '';

    public function mount(): void
    {
        $user = Auth::user();
        if ($user) {
            $opWallet = Wallet::where('user_id', $user->id)->where('type', 'operational')->first();
            $this->sourceWalletId = $opWallet?->id;
        }
    }

    #[On('transaction-saved')]
    public function refreshWallets(): void
    {
        // Re-renders component
    }

    // --- CREATE / EDIT / DELETE WALLET METHODS ---
    public function openCreateModal(): void
    {
        $this->reset(['editingWalletId', 'name', 'accountLabel', 'type', 'initialBalance', 'targetAmount', 'dailyLimit']);
        $this->type = 'trip';
        $this->resetValidation();
        $this->isCreateModalOpen = true;
    }

    public function openEditModal(int $walletId): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $wallet = Wallet::where('user_id', $user->id)->findOrFail($walletId);
        $this->editingWalletId = $wallet->id;
        $this->name = $wallet->name;
        $this->accountLabel = $wallet->account_label ?? '';
        $this->type = $wallet->type;
        $this->initialBalance = (float) $wallet->current_balance;
        $this->targetAmount = $wallet->target_amount ? (float) $wallet->target_amount : null;
        $this->dailyLimit = $wallet->daily_limit ? (float) $wallet->daily_limit : null;
        $this->resetValidation();
        $this->isCreateModalOpen = true;
    }

    public function closeCreateModal(): void
    {
        $this->isCreateModalOpen = false;
        $this->editingWalletId = null;
    }

    public function saveWallet(): void
    {
        if ($this->initialBalance) {
            $this->initialBalance = $this->parseNominal($this->initialBalance);
        }
        if ($this->targetAmount) {
            $this->targetAmount = $this->parseNominal($this->targetAmount);
        }
        if ($this->dailyLimit) {
            $this->dailyLimit = $this->parseNominal($this->dailyLimit);
        }

        $this->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:operational,trip,saving',
            'initialBalance' => 'nullable|numeric|min:0|max:1000000000',
            'targetAmount' => 'nullable|numeric|min:0|max:1000000000',
            'dailyLimit' => 'nullable|numeric|min:0|max:1000000000',
            'accountLabel' => 'nullable|string|max:50',
        ], [
            'name.required' => 'Nama dompet / target wajib diisi.',
        ]);

        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        if ($this->editingWalletId) {
            $wallet = Wallet::where('user_id', $user->id)->findOrFail($this->editingWalletId);
            $wallet->update([
                'name' => $this->name,
                'account_label' => $this->accountLabel ?: null,
                'target_amount' => $wallet->type !== 'operational' ? ($this->targetAmount ?: null) : null,
                'daily_limit' => $wallet->type === 'operational' ? ($this->dailyLimit ?: null) : null,
            ]);

            $this->isCreateModalOpen = false;
            $this->editingWalletId = null;
            $this->dispatch('transaction-saved');
            $this->dispatch('notify', message: 'Dompet berhasil diperbarui!');
            return;
        }

        DB::transaction(function () use ($user) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'name' => $this->name,
                'account_label' => $this->accountLabel ?: null,
                'type' => $this->type,
                'current_balance' => $this->initialBalance ?? 0,
                'target_amount' => $this->targetAmount ?: null,
                'daily_limit' => $this->dailyLimit ?: null,
                'status' => 'active',
            ]);

            if ($this->initialBalance > 0) {
                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'category_id' => null,
                    'type' => 'income',
                    'amount' => $this->initialBalance,
                    'title' => 'Saldo Awal ' . $wallet->name,
                    'note' => 'Inisialisasi dompet baru',
                    'transaction_date' => Carbon::now(),
                ]);
            }
        });

        $this->isCreateModalOpen = false;
        $this->dispatch('transaction-saved');
        $this->dispatch('notify', message: 'Dompet baru berhasil dibuat!');
    }

    public function deleteWallet(int $walletId): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $wallet = Wallet::where('user_id', $user->id)
            ->withCount(['transactions', 'incomingTransfers'])
            ->findOrFail($walletId);

        if ($wallet->transactions_count > 0 || $wallet->incoming_transfers_count > 0) {
            $this->dispatch('notify', message: 'Dompet yang memiliki riwayat transaksi tidak dapat dihapus.', type: 'error');
            return;
        }

        $wallet->delete();
        $this->dispatch('transaction-saved');
        $this->dispatch('notify', message: 'Dompet berhasil dihapus.');
    }

    // --- DEPOSIT / TAMBAH SALDO TABUNGAN METHODS ---
    #[On('open-deposit-modal')]
    public function openDepositModal(?int $walletId = null): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        if ($walletId) {
            $wallet = Wallet::where('user_id', $user->id)->find($walletId);
            $walletId = $wallet?->id;
        }

        if (!$walletId) {
            $firstGoal = Wallet::where('user_id', $user->id)->whereIn('type', ['trip', 'saving'])->first();
            $walletId = $firstGoal?->id;
        }

        $this->depositWalletId = $walletId;
        $this->depositAmount = 100000;
        $this->sourceMode = 'operational';
        $this->depositNote = '';

        $opWallet = Wallet::where('user_id', $user->id)->where('type', 'operational')->first();
        $this->sourceWalletId = $opWallet?->id;

        $this->isDepositModalOpen = true;
    }

    public function closeDepositModal(): void
    {
        $this->isDepositModalOpen = false;
    }

    public function addDepositAmount(int|float $val): void
    {
        $current = $this->parseNominal($this->depositAmount);
        $this->depositAmount = $current + $val;
    }

    public function saveDeposit(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $this->depositAmount = $this->parseNominal($this->depositAmount);

        $rules = [
            'depositWalletId' => [
                'required',
                Rule::exists('wallets', 'id')->where('user_id', $user->id),
            ],
            'depositAmount' => 'required|numeric|min:1000|max:1000000000',
            'sourceMode' => 'required|in:operational,external',
        ];

        if ($this->sourceMode === 'operational') {
            $rules['sourceWalletId'] = [
                'required',
                Rule::exists('wallets', 'id')->where('user_id', $user->id),
                'different:depositWalletId',
            ];
        }

        $this->validate($rules, [
            'depositWalletId.required' => 'Pilih target tabungan yang ingin disetor.',
            'depositWalletId.exists' => 'Target tabungan yang dipilih tidak valid.',
            'depositAmount.min' => 'Nominal tabungan minimal Rp 1.000.',
            'depositAmount.max' => 'Nominal tabungan maksimal Rp 1.000.000.000.',
            'sourceWalletId.required' => 'Pilih rekening kas sumber dana.',
            'sourceWalletId.exists' => 'Rekening sumber dana tidak valid.',
            'sourceWalletId.different' => 'Sumber dana tidak boleh sama dengan target tabungan.',
        ]);

        DB::transaction(function () use ($user) {
            $targetWallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->findOrFail($this->depositWalletId);

            if ($this->sourceMode === 'operational') {
                $sourceWallet = Wallet::where('user_id', $user->id)
                    ->lockForUpdate()
                    ->findOrFail($this->sourceWalletId);

                if ((float) $sourceWallet->current_balance < (float) $this->depositAmount) {
                    throw ValidationException::withMessages([
                        'depositAmount' => 'Saldo rekening sumber tidak mencukupi (tersedia: ' . $sourceWallet->formatted_balance . ').',
                    ]);
                }

                // Transfer allocation
                $sourceWallet->decrement('current_balance', $this->depositAmount);
                $targetWallet->increment('current_balance', $this->depositAmount);

                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $sourceWallet->id,
                    'destination_wallet_id' => $targetWallet->id,
                    'category_id' => null,
                    'type' => 'transfer',
                    'amount' => $this->depositAmount,
                    'title' => 'Nabung ke ' . $targetWallet->name,
                    'note' => $this->depositNote ?: 'Disisihkan dari ' . $sourceWallet->name,
                    'transaction_date' => Carbon::now(),
                ]);
            } else {
                // External income directly added to this goal
                $targetWallet->increment('current_balance', $this->depositAmount);

                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $targetWallet->id,
                    'category_id' => null,
                    'type' => 'income',
                    'amount' => $this->depositAmount,
                    'title' => 'Setor Tabungan ' . $targetWallet->name,
                    'note' => $this->depositNote ?: 'Pemasukan tambahan untuk tabungan',
                    'transaction_date' => Carbon::now(),
                ]);
            }
        });

        $this->isDepositModalOpen = false;
        $this->dispatch('transaction-saved');
        $this->dispatch('notify', message: 'Tabungan berhasil ditambahkan!');
    }

    // --- TRANSFER / CAIRKAN DANA ANTAR DOMPET ---
    public function openTransferModal(?int $fromWalletId = null): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $userWallets = Wallet::where('user_id', $user->id)->get();
        $source = $fromWalletId
            ? $userWallets->firstWhere('id', $fromWalletId)
            : ($userWallets->whereIn('type', ['trip', 'saving'])->first() ?? $userWallets->first());

        $destination = $userWallets->where('id', '!=', $source?->id)->where('type', 'operational')->first()
            ?? $userWallets->where('id', '!=', $source?->id)->first();

        $this->fromWalletId = $source?->id;
        $this->toWalletId = $destination?->id;
        $this->transferAmount = 100000;
        $this->transferNote = '';
        $this->resetValidation();
        $this->isTransferModalOpen = true;
    }

    public function closeTransferModal(): void
    {
        $this->isTransferModalOpen = false;
    }

    public function saveTransfer(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $this->transferAmount = $this->parseNominal($this->transferAmount);

        $this->validate([
            'fromWalletId' => [
                'required',
                Rule::exists('wallets', 'id')->where('user_id', $user->id),
            ],
            'toWalletId' => [
                'required',
                Rule::exists('wallets', 'id')->where('user_id', $user->id),
                'different:fromWalletId',
            ],
            'transferAmount' => 'required|numeric|min:1000|max:1000000000',
            'transferNote' => 'nullable|string|max:255',
        ], [
            'fromWalletId.required' => 'Pilih dompet asal dana.',
            'toWalletId.required' => 'Pilih dompet tujuan.',
            'toWalletId.different' => 'Dompet asal dan tujuan harus berbeda.',
            'transferAmount.min' => 'Nominal transfer minimal Rp 1.000.',
        ]);

        DB::transaction(function () use ($user) {
            $ids = collect([(int) $this->fromWalletId, (int) $this->toWalletId])->sort()->values();
            $wallets = Wallet::where('user_id', $user->id)
                ->whereIn('id', $ids)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $fromWallet = $wallets->get((int) $this->fromWalletId);
            $toWallet = $wallets->get((int) $this->toWalletId);

            if (!$fromWallet || !$toWallet) {
                throw ValidationException::withMessages([
                    'fromWalletId' => 'Dompet yang dipilih tidak valid.',
                ]);
            }

            if ((float) $fromWallet->current_balance < (float) $this->transferAmount) {
                throw ValidationException::withMessages([
                    'transferAmount' => 'Saldo dompet asal tidak mencukupi (tersedia: ' . $fromWallet->formatted_balance . ').',
                ]);
            }

            $fromWallet->decrement('current_balance', $this->transferAmount);
            $toWallet->increment('current_balance', $this->transferAmount);

            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $fromWallet->id,
                'destination_wallet_id' => $toWallet->id,
                'category_id' => null,
                'type' => 'transfer',
                'amount' => $this->transferAmount,
                'title' => 'Transfer ' . $fromWallet->name . ' ke ' . $toWallet->name,
                'note' => $this->transferNote ?: 'Pemindahan saldo antar dompet',
                'transaction_date' => Carbon::now(),
            ]);
        });

        $this->isTransferModalOpen = false;
        $this->dispatch('transaction-saved');
        $this->dispatch('notify', message: 'Transfer saldo antar dompet berhasil!');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $user = Auth::user();
        $userId = $user?->id;

        $wallets = Wallet::withCount(['transactions', 'incomingTransfers'])
            ->where('user_id', $userId)
            ->get();

        $totalBalance = $wallets->sum('current_balance');
        $tripWallets = $wallets->where('type', 'trip');
        $savingWallets = $wallets->where('type', 'saving');
        $operationalWallets = $wallets->where('type', 'operational');

        $totalSavings = $tripWallets->sum('current_balance') + $savingWallets->sum('current_balance');
        $totalTargets = $tripWallets->sum('target_amount') + $savingWallets->sum('target_amount');
        $overallProgress = $totalTargets > 0 ? round(($totalSavings / $totalTargets) * 100) : 0;

        $depositTargetWallet = $wallets->firstWhere('id', $this->depositWalletId);
        $sourceOperationalWallet = $wallets->firstWhere('id', $this->sourceWalletId);

        return view('livewire.wallets.index', [
            'wallets' => $wallets,
            'totalBalance' => $totalBalance,
            'totalSavings' => $totalSavings,
            'totalTargets' => $totalTargets,
            'overallProgress' => $overallProgress,
            'tripWallets' => $tripWallets,
            'savingWallets' => $savingWallets,
            'operationalWallets' => $operationalWallets,
            'depositTargetWallet' => $depositTargetWallet,
            'sourceOperationalWallet' => $sourceOperationalWallet,
        ]);
    }
}
