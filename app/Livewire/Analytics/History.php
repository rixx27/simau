<?php

namespace App\Livewire\Analytics;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\TransactionLedgerService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class History extends Component
{
    use WithPagination;

    public string $period = '';
    public ?int $selectedWeek = null; // 1, 2, 3, 4 (null for all / this_week)
    public string $search = '';
    public string $typeFilter = 'all'; // all, expense, income, transfer
    public ?int $walletFilter = null;
    public ?int $categoryFilter = null;
    public bool $showFilters = false;
    public bool $showAllCategories = false;

    public function mount(): void
    {
        $now = Carbon::now();
        $this->period = $now->format('Y-m');
        $this->selectedWeek = min(4, (int) ceil($now->day / 7));
    }

    /**
     * @return array<string, string>
     */
    public function getPeriodOptions(): array
    {
        $now = Carbon::now()->startOfMonth();

        return [
            'this_week' => 'Minggu Ini',
            $now->copy()->subMonths(2)->format('Y-m') => $now->copy()->subMonths(2)->translatedFormat('F Y'),
            $now->copy()->subMonth()->format('Y-m') => $now->copy()->subMonth()->translatedFormat('F Y'),
            $now->format('Y-m') => $now->translatedFormat('F Y'),
        ];
    }

    public function selectPeriod(string $period): void
    {
        if (!array_key_exists($period, $this->getPeriodOptions())) {
            return;
        }

        $this->period = $period;
        if ($period === 'this_week') {
            $this->selectedWeek = null;
        } elseif ($this->selectedWeek === null) {
            $this->selectedWeek = 1;
        }

        $this->dispatchChartUpdate();
        $this->dispatch('week-selected', week: $this->selectedWeek);
        $this->resetPage();
    }

    public function selectWeek(?int $week): void
    {
        if ($this->period === 'this_week') {
            return;
        }

        $this->selectedWeek = $this->selectedWeek === $week ? null : $week;
        $this->dispatch('week-selected', week: $this->selectedWeek);
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedWalletFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function toggleFilters(): void
    {
        $this->showFilters = !$this->showFilters;
    }

    public function toggleAllCategories(): void
    {
        $this->showAllCategories = !$this->showAllCategories;
    }

    public function selectCategoryFilter(int $categoryId): void
    {
        $this->categoryFilter = $this->categoryFilter === $categoryId ? null : $categoryId;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'typeFilter', 'walletFilter', 'categoryFilter']);
        $this->resetPage();
    }

    public function deleteTransaction(int $transactionId): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        try {
            app(TransactionLedgerService::class)->deleteTransaction($user, $transactionId);
            $this->dispatch('transaction-saved');
            $this->dispatch('notify', message: 'Transaksi berhasil dihapus dan saldo disesuaikan.');
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? 'Transaksi tidak dapat dihapus.';
            $this->dispatch('notify', message: $message, type: 'error');
        }
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

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function getPeriodDateRange(): array
    {
        if ($this->period === 'this_week') {
            $now = Carbon::now();
            return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
        }

        $monthDate = preg_match('/^\d{4}-\d{2}$/', $this->period)
            ? Carbon::createFromFormat('Y-m', $this->period)->startOfMonth()
            : Carbon::now()->startOfMonth();

        return [$monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth()];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function getChartData(): array
    {
        $user = Auth::user();
        $userId = $user?->id;
        [$startDate, $endDate] = $this->getPeriodDateRange();

        if ($this->period === 'this_week') {
            $labels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            $values = [];
            for ($i = 0; $i < 7; $i++) {
                $day = $startDate->copy()->addDays($i)->toDateString();
                $values[] = (float) Transaction::where('user_id', $userId)
                    ->where('type', 'expense')
                    ->whereDate('transaction_date', $day)
                    ->sum('amount');
            }

            return [
                'labels' => $labels,
                'values' => $values,
            ];
        }

        $year = $startDate->year;
        $month = $startDate->month;

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
            'values' => [(float) $min1, (float) $min2, (float) $min3, (float) $min4],
        ];
    }

    public function render(): View
    {
        $user = Auth::user();
        $userId = $user?->id;
        [$startDate, $endDate] = $this->getPeriodDateRange();
        $periodOptions = $this->getPeriodOptions();
        $periodLabel = $periodOptions[$this->period] ?? $startDate->translatedFormat('F Y');

        // Base Query
        $baseExpenseQuery = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        $totalMonthExpense = (clone $baseExpenseQuery)->sum('amount');
        $divisor = $this->period === 'this_week' ? 7 : 4;
        $weeklyAverage = $totalMonthExpense > 0 ? round($totalMonthExpense / $divisor) : 0;

        // Categories with total for this period
        $categories = Category::where(function ($q) use ($userId) {
            $q->whereNull('user_id')->orWhere('user_id', $userId);
        })->withSum(['transactions' => function ($q) use ($userId, $startDate, $endDate) {
            $q->where('user_id', $userId)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$startDate, $endDate]);
        }], 'amount')
            ->orderByDesc('transactions_sum_amount')
            ->get();

        // Recent activity matching period, week, search, and filters
        $activityQuery = Transaction::with(['category', 'wallet', 'destinationWallet'])
            ->where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($this->period !== 'this_week' && $this->selectedWeek) {
            $startDay = ($this->selectedWeek - 1) * 7 + 1;
            $endDay = $this->selectedWeek === 4 ? $endDate->day : $this->selectedWeek * 7;
            $activityQuery->whereDay('transaction_date', '>=', $startDay)
                ->whereDay('transaction_date', '<=', $endDay);
        }

        $trimmedSearch = trim($this->search);
        if ($trimmedSearch !== '') {
            $activityQuery->where(function ($q) use ($trimmedSearch) {
                $q->where('title', 'like', '%' . $trimmedSearch . '%')
                    ->orWhere('note', 'like', '%' . $trimmedSearch . '%');
            });
        }

        if (in_array($this->typeFilter, ['expense', 'income', 'transfer'], true)) {
            $activityQuery->where('type', $this->typeFilter);
        }

        if ($this->walletFilter) {
            $activityQuery->where(function ($q) {
                $q->where('wallet_id', $this->walletFilter)
                    ->orWhere('destination_wallet_id', $this->walletFilter);
            });
        }

        if ($this->categoryFilter) {
            $activityQuery->where('category_id', $this->categoryFilter);
        }

        $activities = $activityQuery->orderBy('transaction_date', 'desc')->paginate(10);
        $wallets = Wallet::where('user_id', $userId)->get();

        $chartData = $this->getChartData();

        return view('livewire.analytics.history', [
            'periodOptions' => $periodOptions,
            'periodLabel' => $periodLabel,
            'totalMonthExpense' => $totalMonthExpense,
            'weeklyAverage' => $weeklyAverage,
            'categories' => $categories,
            'wallets' => $wallets,
            'activities' => $activities,
            'chartData' => $chartData,
        ]);
    }
}
