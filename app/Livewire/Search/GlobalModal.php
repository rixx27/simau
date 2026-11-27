<?php

namespace App\Livewire\Search;

use App\Models\Transaction;
use App\Models\Trip;
use App\Models\Wallet;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class GlobalModal extends Component
{
    public bool $isOpen = false;
    public string $query = '';

    #[On('open-global-search')]
    public function openModal(): void
    {
        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->query = '';
    }

    public function render(): View
    {
        $user = Auth::user();
        $userId = $user?->id;
        $trimmed = trim($this->query);

        $transactions = collect();
        $wallets = collect();
        $trips = collect();

        if ($this->isOpen && $userId) {
            if ($trimmed !== '') {
                $transactions = Transaction::with(['category', 'wallet'])
                    ->where('user_id', $userId)
                    ->where(function ($q) use ($trimmed) {
                        $q->where('title', 'like', '%' . $trimmed . '%')
                            ->orWhere('note', 'like', '%' . $trimmed . '%');
                    })
                    ->orderBy('transaction_date', 'desc')
                    ->limit(6)
                    ->get();

                $wallets = Wallet::where('user_id', $userId)
                    ->where(function ($q) use ($trimmed) {
                        $q->where('name', 'like', '%' . $trimmed . '%')
                            ->orWhere('account_label', 'like', '%' . $trimmed . '%');
                    })
                    ->limit(4)
                    ->get();

                $trips = Trip::where('user_id', $userId)
                    ->where(function ($q) use ($trimmed) {
                        $q->where('name', 'like', '%' . $trimmed . '%')
                            ->orWhere('destination', 'like', '%' . $trimmed . '%');
                    })
                    ->limit(4)
                    ->get();
            } else {
                $transactions = Transaction::with(['category', 'wallet'])
                    ->where('user_id', $userId)
                    ->orderBy('transaction_date', 'desc')
                    ->limit(5)
                    ->get();
            }
        }

        return view('livewire.search.global-modal', [
            'trimmed' => $trimmed,
            'transactions' => $transactions,
            'wallets' => $wallets,
            'trips' => $trips,
        ]);
    }
}
