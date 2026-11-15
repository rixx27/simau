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
    public bool $isCreateModalOpen = false;

    // Create wallet form
    public string $name = '';
    public string $accountLabel = '';
    public string $type = 'trip'; // operational, trip, saving
    public ?int $initialBalance = 0;
    public ?int $targetAmount = null;
    public ?int $dailyLimit = null;

    #[On('transaction-saved')]
    public function refreshWallets(): void
    {
        // Re-renders component
    }

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
        $this->dispatch('notify', message: 'Dompet baru berhasil dibuat!');
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

        return view('livewire.wallets.index', [
            'wallets' => $wallets,
            'totalBalance' => $totalBalance,
            'totalSavings' => $totalSavings,
            'totalTargets' => $totalTargets,
            'overallProgress' => $overallProgress,
            'tripWallets' => $tripWallets,
            'savingWallets' => $savingWallets,
            'operationalWallets' => $operationalWallets,
        ]);
    }
}
