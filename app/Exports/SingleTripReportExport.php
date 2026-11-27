<?php

namespace App\Exports;

use App\Models\Trip;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class SingleTripReportExport implements Export, FromView, WithTitle, ShouldAutoSize
{
    use Exportable;

    protected Trip $trip;

    public function __construct(Trip $trip)
    {
        $this->trip = $trip;
    }

    public function view(): View
    {
        $transactions = $this->trip->transactions()
            ->where('type', 'expense')
            ->with(['category', 'wallet'])
            ->orderBy('transaction_date', 'asc')
            ->get();

        $categoryBreakdown = $transactions->groupBy(function ($tx) {
            return $tx->category ? $tx->category->name : 'Lainnya';
        })->map(function ($items, $catName) {
            return [
                'name' => $catName,
                'count' => $items->count(),
                'total' => $items->sum('amount'),
            ];
        });

        $totalSpent = (float) $transactions->sum('amount');
        $surplusDeficit = (float) $this->trip->budget - $totalSpent;

        return view('exports.trip-report', [
            'trip' => $this->trip,
            'transactions' => $transactions,
            'categoryBreakdown' => $categoryBreakdown,
            'totalSpent' => $totalSpent,
            'surplusDeficit' => $surplusDeficit,
        ]);
    }

    public function title(): string
    {
        return 'Rekap ' . substr($this->trip->name, 0, 20);
    }
}
