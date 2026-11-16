<?php

namespace App\Livewire\Wallets;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class DailyLimitModal extends Component
{
    public bool $isOpen = false;
    public ?int $walletId = null;
    public ?Wallet $wallet = null;

    public bool $isEnabled = true;
    public ?int $dailyLimit = 350000;
    public float $todaySpent = 0;

    #[On('open-daily-limit-modal')]
    public function openModal(?int $walletId = null): void
    {
        $user = Auth::user() ?? User::first();
        if (!$user) {
            return;
        }

        if ($walletId) {
            $this->wallet = Wallet::where('user_id', $user->id)->find($walletId);
        }

        if (!$this->wallet) {
            $this->wallet = Wallet::where('user_id', $user->id)
                ->where('type', 'operational')
                ->first() ?? Wallet::where('user_id', $user->id)->first();
        }

        if (!$this->wallet) {
            return;
        }

        $this->walletId = $this->wallet->id;
        $this->isEnabled = (bool) ($this->wallet->daily_limit && $this->wallet->daily_limit > 0);
        $this->dailyLimit = $this->wallet->daily_limit ? (int) $this->wallet->daily_limit : 350000;

        // Hitung pengeluaran hari ini dari dompet ini
        $this->todaySpent = (float) Transaction::where('wallet_id', $this->wallet->id)
            ->where('type', 'expense')
            ->whereDate('transaction_date', Carbon::today())
            ->sum('amount');

        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
    }

    public function setPreset(int $amount): void
    {
        $this->dailyLimit = $amount;
        $this->isEnabled = true;
    }

    public function addAmount(int $amount): void
    {
        $this->dailyLimit = ($this->dailyLimit ?? 0) + $amount;
        $this->isEnabled = true;
    }

    public function toggleEnabled(): void
    {
        $this->isEnabled = !$this->isEnabled;
        if ($this->isEnabled && (!$this->dailyLimit || $this->dailyLimit <= 0)) {
            $this->dailyLimit = 350000;
        }
    }

    public function save(): void
    {
        if (!$this->wallet) {
            return;
        }

        if ($this->isEnabled) {
            $this->validate([
                'dailyLimit' => 'required|numeric|min:10000|max:100000000',
            ], [
                'dailyLimit.required' => 'Masukkan nominal batas harian.',
                'dailyLimit.min' => 'Batas harian minimal Rp 10.000.',
                'dailyLimit.max' => 'Batas harian maksimal Rp 100.000.000.',
            ]);

            $this->wallet->daily_limit = $this->dailyLimit;
        } else {
            $this->wallet->daily_limit = null;
        }

        $this->wallet->save();

        $this->dispatch('transaction-saved');
        $this->dispatch('daily-limit-updated', walletId: $this->wallet->id, limit: $this->wallet->daily_limit);
        $this->dispatch('notify', message: 'Batas pengeluaran harian berhasil disimpan!');

        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.wallets.daily-limit-modal');
    }
}
