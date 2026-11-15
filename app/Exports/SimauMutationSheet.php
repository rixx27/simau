<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class SimauMutationSheet implements FromView, WithTitle, ShouldAutoSize
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
        $transactions = Transaction::with(['wallet', 'destinationWallet', 'category'])
            ->where('user_id', $this->user->id)
            ->whereYear('transaction_date', $this->year)
            ->whereMonth('transaction_date', $this->month)
            ->orderBy('transaction_date', 'desc')
            ->get();

        return view('exports.mutations', [
            'transactions' => $transactions,
            'user' => $this->user,
        ]);
    }

    public function title(): string
    {
        return 'Detail Mutasi';
    }
}
