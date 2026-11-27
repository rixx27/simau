<?php

namespace App\Livewire\Wallets;

use App\Concerns\ParsesNominal;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class DailyLimitModal extends Component
{
    use ParsesNominal;

    public bool $isOpen = false;
    public ?int $walletId = null;
    public ?Wallet $wallet = null;

    public bool $isEnabled = true;
    public mixed $dailyLimit = 350000;
    public float $todaySpent = 0;

    #[On('open-daily-limit-modal')]
    public function openModal(?int $walletId = null): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
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
        $this->dailyLimit = $this->wallet->daily_limit ? (float) $this->wallet->daily_limit : 350000;

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

    public function setPreset(int|float $amount): void
    {
        $this->dailyLimit = $amount;
        $this->isEnabled = true;
    }

    public function addAmount(int|float $amount): void
    {
        $current = $this->parseNominal($this->dailyLimit);
        $this->dailyLimit = $current + $amount;
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
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        if (!$this->walletId) {
            return;
        }

        $wallet = Wallet::where('user_id', $user->id)->findOrFail($this->walletId);
        $this->wallet = $wallet;

        if ($this->isEnabled) {
            $this->dailyLimit = $this->parseNominal($this->dailyLimit);

            $this->validate([
                'dailyLimit' => 'required|numeric|min:1000|max:100000000',
            ], [
                'dailyLimit.required' => 'Masukkan nominal batas harian.',
                'dailyLimit.min' => 'Batas harian minimal Rp 1.000.',
                'dailyLimit.max' => 'Batas harian maksimal Rp 100.000.000.',
            ]);

            $wallet->daily_limit = $this->dailyLimit;
        } else {
            $wallet->daily_limit = null;
        }

        $wallet->save();

        $this->dispatch('transaction-saved');
        $this->dispatch('daily-limit-updated', walletId: $wallet->id, limit: $wallet->daily_limit);
        $this->dispatch('notify', message: 'Batas pengeluaran harian berhasil disimpan!');

        $this->closeModal();
    }

    public function render(): View
    {
        return view('livewire.wallets.daily-limit-modal');
    }
}
