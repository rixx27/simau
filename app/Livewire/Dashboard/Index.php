<?php

namespace App\Livewire\Dashboard;

use App\Exports\SimauReportExport;
use App\Models\Transaction;
use App\Models\Trip;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function exportExcel(): BinaryFileResponse
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $fileName = 'SIMAU_Report_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new SimauReportExport($user), $fileName);
    }

    public function render(): View
    {
        $user = Auth::user();
        $userId = $user?->id;

        // Wallets
        $wallets = Wallet::where('user_id', $userId)->get();
        $totalBalance = $wallets->sum('current_balance');
        $operationalWallet = $wallets->where('type', 'operational')->first();

        // Calculate Batas Harian (Daily Limit)
        $dailyLimit = $operationalWallet?->daily_limit;
        $referenceDate = Carbon::today()->toDateString();

        // Daily expenses from operational wallet today
        $todayExpenses = $operationalWallet
            ? Transaction::where('user_id', $userId)
                ->where('wallet_id', $operationalWallet->id)
                ->where('type', 'expense')
                ->whereDate('transaction_date', $referenceDate)
                ->sum('amount')
            : 0;

        $remainingDaily = $dailyLimit ? max(0, $dailyLimit - $todayExpenses) : 0;
        $dailyPercentRemaining = ($dailyLimit && $dailyLimit > 0)
            ? round(($remainingDaily / $dailyLimit) * 100)
            : 0;

        // Active Trip or Saving Goal
        $activeTrip = Trip::where('user_id', $userId)
            ->where('status', 'active')
            ->latest()
            ->first();

        $savingGoal = $wallets->whereIn('type', ['saving', 'trip'])->first();

        if ($activeTrip) {
            $tripName = $activeTrip->name;
            $tripSpent = (float) $activeTrip->total_spent;
            $tripBudget = (float) $activeTrip->budget;
            $tripPercent = $tripBudget > 0 ? min(100, round(($tripSpent / $tripBudget) * 100)) : 0;
        } elseif ($savingGoal) {
            $tripName = $savingGoal->name;
            $tripSpent = (float) $savingGoal->current_balance;
            $tripBudget = (float) ($savingGoal->target_amount ?: 5000000);
            $tripPercent = $tripBudget > 0 ? min(100, round(($tripSpent / $tripBudget) * 100)) : 0;
        } else {
            $tripName = 'Target Tabungan';
            $tripSpent = 0;
            $tripBudget = 5000000;
            $tripPercent = 0;
        }

        // Balance Breakdown for Hero Card Sub-badge
        $operationalBalance = (float) $wallets->where('type', 'operational')->sum('current_balance');
        $tripBalance = (float) $wallets->whereIn('type', ['trip', 'saving'])->sum('current_balance');
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

        $passkeysCount = $user ? $user->passkeys()->count() : 0;
        $hasTwoFactor = (bool) $user?->two_factor_confirmed_at;

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
            'passkeysCount' => $passkeysCount,
            'hasTwoFactor' => $hasTwoFactor,
        ]);
    }
}
