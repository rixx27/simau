<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class SimauSummarySheet implements FromView, WithTitle, ShouldAutoSize
{
    protected User $user;
    protected int $month;
    protected int $year;

    public function __construct(User $user, int $month, int $year)
    {
        $this->user = $user;
        $this->month = $month;
        $this->year = $year;
    }

    public function view(): View
    {
        $monthName = Carbon::createFromDate($this->year, $this->month, 1)->translatedFormat('F Y');

        $wallets = Wallet::where('user_id', $this->user->id)->get();
        $totalBalance = $wallets->sum('current_balance');
        $operationalBalance = $wallets->where('type', 'operational')->sum('current_balance');
        $tripBalance = $wallets->where('type', 'trip')->sum('current_balance');

        $transactionsQuery = Transaction::where('user_id', $this->user->id)
            ->whereYear('transaction_date', $this->year)
            ->whereMonth('transaction_date', $this->month);

        $totalExpense = (clone $transactionsQuery)->where('type', 'expense')->sum('amount');
        $totalIncome = (clone $transactionsQuery)->where('type', 'income')->sum('amount');
        $totalTransfer = (clone $transactionsQuery)->where('type', 'transfer')->sum('amount');

        $categoryExpenses = Category::withSum(['transactions' => function ($q) {
            $q->where('user_id', $this->user->id)
                ->where('type', 'expense')
                ->whereYear('transaction_date', $this->year)
                ->whereMonth('transaction_date', $this->month);
        }], 'amount')->get();

        return view('exports.summary', [
            'user' => $this->user,
            'monthName' => $monthName,
            'totalBalance' => $totalBalance,
            'operationalBalance' => $operationalBalance,
            'tripBalance' => $tripBalance,
            'totalExpense' => $totalExpense,
            'totalIncome' => $totalIncome,
            'totalTransfer' => $totalTransfer,
            'categoryExpenses' => $categoryExpenses,
            'wallets' => $wallets,
        ]);
    }

    public function title(): string
    {
        return 'Ringkasan';
    }
}
