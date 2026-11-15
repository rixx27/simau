<?php

namespace App\Livewire\Wallets;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    // Create new wallet / target modal
    public bool $isCreateModalOpen = false;
    public string $name = '';
    public string $accountLabel = '';
    public string $type = 'trip'; // operational, trip, saving
    public ?int $initialBalance = 0;
    public ?int $targetAmount = null;
    public ?int $dailyLimit = null;

    // Deposit / Tambah Saldo Tabungan modal
    public bool $isDepositModalOpen = false;
    public ?int $depositWalletId = null;
    public ?int $depositAmount = 100000;
    public string $sourceMode = 'operational'; // 'operational' (sisihkan kas) or 'external' (dana masuk baru)
    public ?int $sourceWalletId = null;
    public string $depositNote = '';

    public function mount(): void
    {
        $user = Auth::user() ?? User::first();
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

    // --- CREATE NEW WALLET METHODS ---
    public function openCreateModal(): void
    {
        $this->reset(['name', 'accountLabel', 'type', 'initialBalance', 'targetAmount', 'dailyLimit']);
        $this->type = 'trip';
        $this->isCreateModalOpen = true;
    }

    public function closeCreateModal(): void
    {
        $this->isCreateModalOpen = false;
    }

    public function saveWallet(): void
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:operational,trip,saving',
            'initialBalance' => 'nullable|numeric|min:0',
            'targetAmount' => 'nullable|numeric|min:0',
            'dailyLimit' => 'nullable|numeric|min:0',
            'accountLabel' => 'nullable|string|max:50',
        ], [
            'name.required' => 'Nama dompet / target wajib diisi.',
        ]);

        $user = Auth::user() ?? User::first();
        if (!$user) {
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

    // --- DEPOSIT / TAMBAH SALDO TABUNGAN METHODS ---
    #[On('open-deposit-modal')]
    public function openDepositModal(?int $walletId = null): void
    {
        $user = Auth::user() ?? User::first();
        if (!$user) {
            return;
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

    public function addDepositAmount(int $val): void
    {
        $this->depositAmount = ($this->depositAmount ?? 0) + $val;
    }

    public function saveDeposit(): void
    {
        $rules = [
            'depositWalletId' => 'required|exists:wallets,id',
            'depositAmount' => 'required|numeric|min:5000',
            'sourceMode' => 'required|in:operational,external',
        ];

        if ($this->sourceMode === 'operational') {
            $rules['sourceWalletId'] = 'required|exists:wallets,id|different:depositWalletId';
        }

        $this->validate($rules, [
            'depositWalletId.required' => 'Pilih target tabungan yang ingin disetor.',
            'depositAmount.min' => 'Nominal tabungan minimal Rp 5.000.',
            'sourceWalletId.required' => 'Pilih rekening kas sumber dana.',
            'sourceWalletId.different' => 'Sumber dana tidak boleh sama dengan target tabungan.',
        ]);

        $user = Auth::user() ?? User::first();
        if (!$user) {
            return;
        }

        DB::transaction(function () use ($user) {
            $targetWallet = Wallet::lockForUpdate()->findOrFail($this->depositWalletId);

            if ($this->sourceMode === 'operational') {
                $sourceWallet = Wallet::lockForUpdate()->findOrFail($this->sourceWalletId);

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

    public function render()
    {
        $user = Auth::user() ?? User::first();
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
