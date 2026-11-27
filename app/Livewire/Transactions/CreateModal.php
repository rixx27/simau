<?php

namespace App\Livewire\Transactions;

use App\Concerns\ParsesNominal;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Trip;
use App\Models\Wallet;
use App\Services\TransactionLedgerService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateModal extends Component
{
    use ParsesNominal;

    public bool $isOpen = false;
    public ?int $editingTransactionId = null;

    // Form fields
    public string $mode = 'daily'; // 'daily' (Pengeluaran Harian) or 'trip' (Pengeluaran Trip)
    public mixed $amount = 45000;
    public ?int $selectedCategoryId = null;
    public ?int $selectedWalletId = null;
    public ?int $selectedTripId = null;
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
        $user = Auth::user();
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

        // Set default trip (active trip first)
        if (!$this->selectedTripId) {
            $activeTrip = Trip::where('user_id', $user->id)
                ->where('status', 'active')
                ->latest()
                ->first() ?? Trip::where('user_id', $user->id)->latest()->first();

            $this->selectedTripId = $activeTrip?->id;
        }

        // Set default category
        if (!$this->selectedCategoryId) {
            $grabCategory = Category::where(function ($query) use ($user) {
                $query->whereNull('user_id')->orWhere('user_id', $user->id);
            })->where('name', 'GrabCar')->first()
                ?? Category::where(function ($query) use ($user) {
                    $query->whereNull('user_id')->orWhere('user_id', $user->id);
                })->first();
            $this->selectedCategoryId = $grabCategory?->id;
        }
    }

    #[On('open-transaction-modal')]
    public function openModal(string $mode = 'daily', ?int $walletId = null, ?int $tripId = null): void
    {
        $this->editingTransactionId = null;
        $this->note = '';
        $this->transactionDate = Carbon::now()->format('Y-m-d\TH:i');
        $this->mode = in_array($mode, ['daily', 'income', 'trip']) ? $mode : 'daily';
        $this->loadDefaults();

        if ($walletId) {
            $this->selectedWalletId = $walletId;
        }
        if ($tripId) {
            $this->selectedTripId = $tripId;
            $this->mode = 'trip';
        }

        $this->setMode($this->mode);

        $this->isOpen = true;
    }

    #[On('open-edit-transaction-modal')]
    public function openEditModal(int $transactionId): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $tx = Transaction::where('user_id', $user->id)->findOrFail($transactionId);
        if ($tx->type === 'transfer') {
            $this->dispatch('notify', message: 'Transaksi transfer tabungan hanya dapat dihapus untuk menjaga kesesuaian dua dompet.', type: 'error');
            return;
        }

        $this->editingTransactionId = $tx->id;
        $this->mode = $tx->type === 'income' ? 'income' : ($tx->trip_id ? 'trip' : 'daily');
        $this->amount = (float) $tx->amount;
        $this->selectedWalletId = $tx->wallet_id;
        $this->selectedCategoryId = $tx->category_id;
        $this->selectedTripId = $tx->trip_id;
        $this->title = $tx->title;
        $this->note = $tx->note ?? '';
        $this->transactionDate = $tx->transaction_date->format('Y-m-d\TH:i');
        $this->isCreatingCategory = false;
        $this->showDatePicker = false;
        $this->resetValidation();
        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->editingTransactionId = null;
        $this->isCreatingCategory = false;
        $this->showDatePicker = false;
    }

    public function setMode(string $mode): void
    {
        $this->mode = in_array($mode, ['daily', 'income', 'trip']) ? $mode : 'daily';

        $isIncome = $this->mode === 'income';
        $firstCat = Category::where(function ($query) {
            $query->whereNull('user_id')->orWhere('user_id', Auth::id());
        })->where('type', $isIncome ? 'income' : 'expense')->first();

        $this->selectedCategoryId = $firstCat?->id;

        if ($this->mode === 'trip') {
            $this->title = 'Kuliner / Transport Liburan';
            $this->amount = $this->amount ?: 75000;
        } elseif ($this->mode === 'income') {
            $this->title = 'Pemasukan Kas';
            $this->amount = $this->amount ?: 250000;
        } else {
            $this->title = 'Pengeluaran Harian';
            $this->amount = $this->amount ?: 45000;
        }
    }

    public function addAmount(int|float $value): void
    {
        $current = $this->parseNominal($this->amount);
        $this->amount = $current + $value;
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
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $this->validate([
            'newCategoryName' => 'required|string|max:50',
            'newCategoryColor' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => trim($this->newCategoryName),
            'icon' => $this->newCategoryIcon ?: 'tag',
            'color' => $this->newCategoryColor ?: '#5865F2',
            'type' => $this->mode === 'income' ? 'income' : 'expense',
        ]);

        $this->selectedCategoryId = $category->id;
        $this->newCategoryName = '';
        $this->isCreatingCategory = false;
    }

    public function deleteCategory(int $categoryId): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $category = Category::where('user_id', $user->id)->findOrFail($categoryId);
        $category->delete();

        if ($this->selectedCategoryId === $categoryId) {
            $fallback = Category::where(function ($query) use ($user) {
                $query->whereNull('user_id')->orWhere('user_id', $user->id);
            })->where('type', $this->mode === 'income' ? 'income' : 'expense')->first();

            $this->selectedCategoryId = $fallback?->id;
        }

        $this->dispatch('notify', message: 'Kategori kustom berhasil dihapus.');
    }

    public function save(): void
    {
        $this->amount = $this->parseNominal($this->amount);

        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $rules = [
            'amount' => 'required|numeric|min:1000|max:1000000000',
            'selectedWalletId' => [
                'required',
                Rule::exists('wallets', 'id')->where('user_id', $user->id),
            ],
            'selectedCategoryId' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query) use ($user) {
                    $query->whereNull('user_id')->orWhere('user_id', $user->id);
                }),
            ],
            'title' => 'required|string|max:150',
            'note' => 'nullable|string|max:500',
            'transactionDate' => 'required|date',
        ];

        if ($this->mode === 'trip') {
            $rules['selectedTripId'] = [
                'required',
                Rule::exists('trips', 'id')->where('user_id', $user->id),
            ];
        }

        $this->validate($rules, [
            'selectedCategoryId.required' => 'Pilih salah satu kategori transaksi.',
            'selectedCategoryId.exists' => 'Kategori yang dipilih tidak valid.',
            'selectedWalletId.exists' => 'Dompet yang dipilih tidak valid.',
            'selectedTripId.required' => 'Pilih trip tujuan pencatatan ini.',
            'selectedTripId.exists' => 'Trip yang dipilih tidak valid.',
            'amount.min' => 'Nominal minimal Rp 1.000.',
            'amount.max' => 'Nominal maksimal Rp 1.000.000.000.',
        ]);

        $isIncome = $this->mode === 'income';
        $category = Category::where(function ($query) use ($user) {
            $query->whereNull('user_id')->orWhere('user_id', $user->id);
        })->findOrFail($this->selectedCategoryId);

        if ($this->editingTransactionId) {
            app(TransactionLedgerService::class)->updateTransaction($user, $this->editingTransactionId, [
                'wallet_id' => (int) $this->selectedWalletId,
                'trip_id' => $this->mode === 'trip' ? $this->selectedTripId : null,
                'category_id' => $category->id,
                'type' => $isIncome ? 'income' : 'expense',
                'amount' => $this->amount,
                'title' => $this->title,
                'note' => $this->note ?: null,
                'transaction_date' => Carbon::parse($this->transactionDate),
            ]);

            $this->dispatch('transaction-saved');
            $this->dispatch('notify', message: 'Transaksi berhasil diperbarui!');
            $this->closeModal();
            return;
        }

        DB::transaction(function () use ($user, $isIncome, $category) {
            $wallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->findOrFail($this->selectedWalletId);

            if (!$isIncome && (float) $wallet->current_balance < (float) $this->amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo dompet tidak mencukupi (tersedia: ' . $wallet->formatted_balance . ').',
                ]);
            }

            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'trip_id' => $this->mode === 'trip' ? $this->selectedTripId : null,
                'category_id' => $category->id,
                'type' => $isIncome ? 'income' : 'expense',
                'amount' => $this->amount,
                'title' => $this->title,
                'note' => $this->note ?: null,
                'transaction_date' => Carbon::parse($this->transactionDate),
            ]);

            if ($isIncome) {
                // Tambah saldo ke dompet kas
                $wallet->increment('current_balance', $this->amount);
            } else {
                // Kurangi saldo dari dompet
                $wallet->decrement('current_balance', $this->amount);
            }
        });

        $this->dispatch('transaction-saved');
        $this->dispatch('notify', message: $isIncome ? 'Pemasukan berhasil ditambahkan ke saldo kas!' : 'Pengeluaran berhasil dicatat!');
        $this->closeModal();
    }

    public function deleteEditingTransaction(): void
    {
        $user = Auth::user();
        if (!$user || !$this->editingTransactionId) {
            return;
        }

        try {
            app(TransactionLedgerService::class)->deleteTransaction($user, $this->editingTransactionId);
            $this->dispatch('transaction-saved');
            $this->dispatch('notify', message: 'Transaksi berhasil dihapus dan saldo disesuaikan.');
            $this->closeModal();
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? 'Transaksi gagal dihapus.';
            $this->addError('amount', $message);
        }
    }

    public function render(): View
    {
        $user = Auth::user();
        $userId = $user?->id;

        $isIncome = $this->mode === 'income';

        $wallets = Wallet::where('user_id', $userId)->get();
        $trips = Trip::where('user_id', $userId)->orderByRaw("CASE WHEN status = 'active' THEN 1 WHEN status = 'planned' THEN 2 ELSE 3 END")->get();
        $categories = Category::where(function ($query) use ($userId) {
            $query->whereNull('user_id')->orWhere('user_id', $userId);
        })->where('type', $isIncome ? 'income' : 'expense')->get();

        $selectedWallet = $wallets->firstWhere('id', $this->selectedWalletId);
        $selectedTrip = $trips->firstWhere('id', $this->selectedTripId);

        return view('livewire.transactions.create-modal', [
            'wallets' => $wallets,
            'trips' => $trips,
            'categories' => $categories,
            'selectedWallet' => $selectedWallet,
            'selectedTrip' => $selectedTrip,
        ]);
    }
}
