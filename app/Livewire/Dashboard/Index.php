<?php

namespace App\Livewire\Dashboard;

use App\Exports\SimauReportExport;
use App\Models\Transaction;
use App\Models\Trip;
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

        // Calculate Batas Harian (Daily Limit)
        $dailyLimit = $operationalWallet?->daily_limit ?? 350000;

        $today = Carbon::now();
        $hasToday = Transaction::where('user_id', $userId)
            ->whereDate('transaction_date', $today->toDateString())
            ->exists();

        $referenceDate = $hasToday ? $today->toDateString() : '2026-11-13';

        // Daily expenses (transactions not tied to trip, or today's operational expenses)
        $todayExpenses = Transaction::where('user_id', $userId)
            ->where('wallet_id', $operationalWallet?->id)
            ->where('type', 'expense')
            ->whereDate('transaction_date', $referenceDate)
            ->sum('amount');

        $remainingDaily = max(0, $dailyLimit - $todayExpenses);
        $dailyPercentRemaining = $dailyLimit > 0 ? round(($remainingDaily / $dailyLimit) * 100) : 0;

        // Active Trip
        $activeTrip = Trip::where('user_id', $userId)
            ->where('status', 'active')
            ->latest()
            ->first();

        // Fallback trip stats if active trip exists
        $tripName = $activeTrip?->name ?? 'Pundi Liburan';
        $tripSpent = $activeTrip ? $activeTrip->total_spent : 4200000;
        $tripBudget = $activeTrip ? (float)$activeTrip->budget : 5000000;
        $tripPercent = $tripBudget > 0 ? round(($tripSpent / $tripBudget) * 100) : 84;

        // Balance Breakdown for Hero Card Sub-badge
        $operationalBalance = (float) $wallets->where('type', 'operational')->sum('current_balance');
        $tripBalance = (float) $wallets->where('type', 'trip')->sum('current_balance');
        $formattedOpShort = $operationalBalance >= 1000000
            ? 'Rp ' . number_format($operationalBalance / 1000000, 1, ',', '.') . ' Jt'
            : 'Rp ' . number_format($operationalBalance, 0, ',', '.');
        $formattedTripShort = $tripBalance >= 1000000
            ? 'Rp ' . number_format($tripBalance / 1000000, 1, ',', '.') . ' Jt'
            : 'Rp ' . number_format($tripBalance, 0, ',', '.');

        // Recent Transactions (limit 8) with trip and category relations
        $recentTransactions = Transaction::with(['category', 'wallet', 'trip'])
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
            'activeTrip' => $activeTrip,
            'tripName' => $tripName,
            'tripSpent' => $tripSpent,
            'tripBudget' => $tripBudget,
            'tripPercent' => $tripPercent,
            'formattedOpShort' => $formattedOpShort,
            'formattedTripShort' => $formattedTripShort,
            'recentTransactions' => $recentTransactions,
            'referenceDate' => $referenceDate,
        ]);
    }
}
