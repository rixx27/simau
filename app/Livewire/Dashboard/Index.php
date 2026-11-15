<?php

namespace App\Livewire\Dashboard;

use App\Exports\SimauReportExport;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public bool $showBalance = true;

    #[On('transaction-saved')]
    public function refreshDashboard(): void
    {
        // Re-renders component automatically
    }

    public function toggleBalance(): void
    {
        $this->showBalance = !$this->showBalance;
    }

    public function exportExcel()
    {
        $user = Auth::user() ?? User::first();
        $fileName = 'SIMAU_Report_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new SimauReportExport($user), $fileName);
    }

    public function render()
    {
        $user = Auth::user() ?? User::first();
        $userId = $user?->id;

        // Wallets
        $wallets = Wallet::where('user_id', $userId)->get();
        $totalBalance = $wallets->sum('current_balance');

        $operationalWallet = $wallets->where('type', 'operational')->first();
        $tripWallet = $wallets->where('type', 'trip')->first();

        // Calculate Batas Harian (Daily Limit)
        $dailyLimit = $operationalWallet?->daily_limit ?? 350000;

        // Today's expenses (using today's date or seed reference 13 Nov 2026)
        $today = Carbon::now();
        // Check if there are transactions today, if not check for November 13, 2026 for demo consistency
        $hasToday = Transaction::where('user_id', $userId)
            ->whereDate('transaction_date', $today->toDateString())
            ->exists();

        $referenceDate = $hasToday ? $today->toDateString() : '2026-11-13';

        $todayExpenses = Transaction::where('user_id', $userId)
            ->where('wallet_id', $operationalWallet?->id)
            ->where('type', 'expense')
            ->whereDate('transaction_date', $referenceDate)
            ->sum('amount');

        $remainingDaily = max(0, $dailyLimit - $todayExpenses);
        $dailyPercentRemaining = $dailyLimit > 0 ? round(($remainingDaily / $dailyLimit) * 100) : 0;

        // Trip Savings Progress
        $tripBalance = $tripWallet?->current_balance ?? 4200000;
        $tripTarget = $tripWallet?->target_amount ?? 5000000;
        $tripPercent = $tripTarget > 0 ? round(($tripBalance / $tripTarget) * 100) : 0;

        // Recent Transactions (limit 8)
        $recentTransactions = Transaction::with(['category', 'wallet'])
            ->where('user_id', $userId)
            ->orderBy('transaction_date', 'desc')
            ->take(8)
            ->get();

        return view('livewire.dashboard.index', [
            'user' => $user,
            'totalBalance' => $totalBalance,
            'formattedTotalBalance' => 'Rp ' . number_format($totalBalance, 0, ',', '.'),
            'dailyLimit' => $dailyLimit,
            'remainingDaily' => $remainingDaily,
            'dailyPercentRemaining' => $dailyPercentRemaining,
            'tripWallet' => $tripWallet,
            'tripBalance' => $tripBalance,
            'tripTarget' => $tripTarget,
            'tripPercent' => $tripPercent,
            'recentTransactions' => $recentTransactions,
            'referenceDate' => $referenceDate,
        ]);
    }
}
