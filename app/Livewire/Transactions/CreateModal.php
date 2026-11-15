<?php

namespace App\Livewire\Transactions;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateModal extends Component
{
    public bool $isOpen = false;

    // Form fields
    public string $mode = 'expense'; // 'expense' or 'saving'
    public ?int $amount = 45000;
    public ?int $selectedCategoryId = null;
    public ?int $selectedWalletId = null;
    public ?int $destinationWalletId = null;
    public string $title = '';
    public string $note = '';
    public string $transactionDate = '';

    // Inline new category creation
    public bool $isCreatingCategory = false;
    public string $newCategoryName = '';
    public string $newCategoryIcon = 'tag';
    public string $newCategoryColor = '#5865F2';

    // Show date picker toggle
    public bool $showDatePicker = false;

    public function mount(): void
    {
        $this->transactionDate = Carbon::now()->format('Y-m-d\TH:i');
        $this->loadDefaults();
    }

    protected function loadDefaults(): void
    {
        $user = Auth::user() ?? User::first();
        if (!$user) {
            return;
        }

        // Set default wallet
        if (!$this->selectedWalletId) {
            $defaultWallet = Wallet::where('user_id', $user->id)
                ->where('type', 'operational')
                ->first() ?? Wallet::where('user_id', $user->id)->first();

            $this->selectedWalletId = $defaultWallet?->id;
        }

        // Set default destination wallet for trip saving
        if (!$this->destinationWalletId) {
            $tripWallet = Wallet::where('user_id', $user->id)
                ->where('type', 'trip')
                ->first();
            $this->destinationWalletId = $tripWallet?->id;
        }

        // Set default category
        if (!$this->selectedCategoryId) {
            $grabCategory = Category::where('name', 'GrabCar')->first()
                ?? Category::first();
            $this->selectedCategoryId = $grabCategory?->id;
        }
    }

    #[On('open-transaction-modal')]
    public function openModal(string $mode = 'expense', ?int $walletId = null, ?int $destinationId = null): void
    {
        $this->mode = $mode;
        $this->loadDefaults();

        if ($walletId) {
            $this->selectedWalletId = $walletId;
        }
        if ($destinationId) {
            $this->destinationWalletId = $destinationId;
        }

        if ($this->mode === 'saving') {
            $this->title = 'Nabung Pundi Liburan';
            $this->amount = 100000;
        } else {
            $this->title = $this->title ?: 'GrabCar ke Malioboro';
            $this->amount = $this->amount ?: 45000;
        }

        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->isCreatingCategory = false;
        $this->showDatePicker = false;
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
        if ($mode === 'saving') {
            if (empty($this->title) || $this->title === 'GrabCar ke Malioboro') {
                $this->title = 'Nabung Pundi Liburan';
            }
        }
    }

    public function addAmount(int $value): void
    {
        $this->amount = ($this->amount ?? 0) + $value;
    }

    public function selectCategory(int $id): void
    {
        $this->selectedCategoryId = $id;
    }

    public function toggleCreateCategory(): void
    {
        $this->isCreatingCategory = !$this->isCreatingCategory;
    }

    public function saveNewCategory(): void
    {
        $this->validate([
            'newCategoryName' => 'required|string|max:50',
            'newCategoryColor' => 'required|string|max:20',
        ]);

        $user = Auth::user() ?? User::first();

        $category = Category::create([
            'user_id' => $user?->id,
            'name' => trim($this->newCategoryName),
            'icon' => $this->newCategoryIcon ?: 'tag',
            'color' => $this->newCategoryColor ?: '#5865F2',
            'type' => 'expense',
        ]);

        $this->selectedCategoryId = $category->id;
        $this->newCategoryName = '';
        $this->isCreatingCategory = false;
    }

    public function save(): void
    {
        $user = Auth::user() ?? User::first();
        if (!$user) {
            return;
        }

        $rules = [
            'amount' => 'required|numeric|min:1000',
            'selectedWalletId' => 'required|exists:wallets,id',
            'title' => 'required|string|max:150',
            'transactionDate' => 'required',
        ];

        if ($this->mode === 'expense') {
            $rules['selectedCategoryId'] = 'required|exists:categories,id';
        } elseif ($this->mode === 'saving') {
            $rules['destinationWalletId'] = 'required|exists:wallets,id|different:selectedWalletId';
        }

        $this->validate($rules, [
            'selectedCategoryId.required' => 'Pilih salah satu kategori transaksi.',
            'destinationWalletId.required' => 'Pilih dompet tujuan tabungan.',
            'destinationWalletId.different' => 'Dompet tujuan harus berbeda dari sumber dana.',
            'amount.min' => 'Nominal minimal Rp 1.000.',
        ]);

        DB::transaction(function () use ($user) {
            $wallet = Wallet::lockForUpdate()->findOrFail($this->selectedWalletId);

            if ($this->mode === 'expense') {
                $category = Category::find($this->selectedCategoryId);

                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'category_id' => $category?->id,
                    'type' => 'expense',
                    'amount' => $this->amount,
                    'title' => $this->title,
                    'note' => $this->note ?: null,
                    'transaction_date' => Carbon::parse($this->transactionDate),
                ]);

                // Deduct from wallet balance
                $wallet->decrement('current_balance', $this->amount);
            } elseif ($this->mode === 'saving') {
                $destWallet = Wallet::lockForUpdate()->findOrFail($this->destinationWalletId);

                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'destination_wallet_id' => $destWallet->id,
                    'category_id' => null,
                    'type' => 'transfer',
                    'amount' => $this->amount,
                    'title' => $this->title ?: 'Nabung Trip ' . $destWallet->name,
                    'note' => $this->note ?: null,
                    'transaction_date' => Carbon::parse($this->transactionDate),
                ]);

                // Transfer balance
                $wallet->decrement('current_balance', $this->amount);
                $destWallet->increment('current_balance', $this->amount);
            }
        });

        $this->dispatch('transaction-saved');
        $this->dispatch('notify', message: 'Transaksi berhasil disimpan!');
        $this->closeModal();
    }

    public function render()
    {
        $user = Auth::user() ?? User::first();
        $userId = $user?->id;

        $wallets = Wallet::where('user_id', $userId)->get();
        $categories = Category::where(function ($query) use ($userId) {
            $query->whereNull('user_id')->orWhere('user_id', $userId);
        })->where('type', 'expense')->get();

        $selectedWallet = $wallets->firstWhere('id', $this->selectedWalletId);
        $destinationWallet = $wallets->firstWhere('id', $this->destinationWalletId);

        return view('livewire.transactions.create-modal', [
            'wallets' => $wallets,
            'categories' => $categories,
            'selectedWallet' => $selectedWallet,
            'destinationWallet' => $destinationWallet,
        ]);
    }
}
