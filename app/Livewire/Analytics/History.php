<?php

namespace App\Livewire\Analytics;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class History extends Component
{
    public string $period = 'November'; // 'Minggu Ini', 'Oktober', 'November', 'Desember'
    public ?int $selectedWeek = 3; // 1, 2, 3, 4 (null for all)

    public function selectPeriod(string $period): void
    {
        $this->period = $period;
        $this->dispatchChartUpdate();
    }

    public function selectWeek(?int $week): void
    {
        $this->selectedWeek = $this->selectedWeek === $week ? null : $week;
        $this->dispatch('week-selected', week: $this->selectedWeek);
    }

    #[On('transaction-saved')]
    public function refreshTransactions(): void
    {
        $this->dispatchChartUpdate();
    }

    protected function dispatchChartUpdate(): void
    {
        $chartData = $this->getChartData();
        $this->dispatch('chart-data-updated', data: $chartData);
    }

    protected function getMonthNumber(): int
    {
        return match ($this->period) {
            'Oktober' => 10,
            'November' => 11,
            'Desember' => 12,
            default => 11,
        };
    }

    public function getChartData(): array
    {
        $user = Auth::user() ?? User::first();
        $userId = $user?->id;
        $month = $this->getMonthNumber();
        $year = 2026;

        // Calculate 4 weekly buckets:
        // Min 1: Days 1 - 7
        // Min 2: Days 8 - 14
        // Min 3: Days 15 - 21
        // Min 4: Days 22 - end of month
        $min1 = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->whereDay('transaction_date', '>=', 1)
            ->whereDay('transaction_date', '<=', 7)
            ->sum('amount');

        $min2 = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->whereDay('transaction_date', '>=', 8)
            ->whereDay('transaction_date', '<=', 14)
            ->sum('amount');

        $min3 = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->whereDay('transaction_date', '>=', 15)
            ->whereDay('transaction_date', '<=', 21)
            ->sum('amount');

        $min4 = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->whereDay('transaction_date', '>=', 22)
            ->sum('amount');

        return [
            'labels' => ['Min 1', 'Min 2', 'Min 3', 'Min 4'],
            'values' => [(float)$min1, (float)$min2, (float)$min3, (float)$min4],
        ];
    }

    public function render()
    {
        $user = Auth::user() ?? User::first();
        $userId = $user?->id;
        $month = $this->getMonthNumber();
        $year = 2026;

        // Base Query
        $baseExpenseQuery = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month);

        $totalMonthExpense = (clone $baseExpenseQuery)->sum('amount');
        $weeklyAverage = $totalMonthExpense > 0 ? round($totalMonthExpense / 4) : 0;

        // Categories with total for this period
        $categories = Category::withSum(['transactions' => function ($q) use ($userId, $year, $month) {
            $q->where('user_id', $userId)
                ->where('type', 'expense')
                ->whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month);
        }], 'amount')
        ->orderByDesc('transactions_sum_amount')
        ->get();

        // Recent activity matching period and optional week
        $activityQuery = Transaction::with(['category', 'wallet'])
            ->where('user_id', $userId)
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month);

        if ($this->selectedWeek) {
            $startDay = ($this->selectedWeek - 1) * 7 + 1;
            $endDay = $this->selectedWeek === 4 ? 31 : $this->selectedWeek * 7;
            $activityQuery->whereDay('transaction_date', '>=', $startDay)
                ->whereDay('transaction_date', '<=', $endDay);
        }

        $activities = $activityQuery->orderBy('transaction_date', 'desc')->get();

        $chartData = $this->getChartData();

        return view('livewire.analytics.history', [
            'totalMonthExpense' => $totalMonthExpense,
            'weeklyAverage' => $weeklyAverage,
            'categories' => $categories,
            'activities' => $activities,
            'chartData' => $chartData,
        ]);
    }
}
