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

    // Create new wallet / target modal
    public bool $isCreateModalOpen = false;
    public string $name = '';
    public string $accountLabel = '';
    public string $type = 'trip'; // operational, trip, saving
    public $initialBalance = 0;
    public $targetAmount = null;
    public $dailyLimit = null;

    // Deposit / Tambah Saldo Tabungan modal
    public bool $isDepositModalOpen = false;
    public ?int $depositWalletId = null;
    public $depositAmount = 100000;
    public string $sourceMode = 'operational'; // 'operational' (sisihkan kas) or 'external' (dana masuk baru)
    public ?int $sourceWalletId = null;
    public string $depositNote = '';

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

    public function render()
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
