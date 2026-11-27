<?php

namespace App\Livewire\Trips;

use App\Exports\SingleTripReportExport;
use App\Models\Transaction;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('components.layouts.app')]
class Report extends Component
{
    public Trip $trip;

    public function mount(Trip $trip): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }
        if ($trip->user_id !== $user->id) {
            abort(403, 'Akses ditolak. Anda hanya dapat melihat rekap perjalanan milik sendiri.');
        }

        $this->trip = $trip;
    }

    public function exportExcel(): BinaryFileResponse
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }
        abort_if($this->trip->user_id !== $user->id, 403);

        $fileName = 'SIMAU_Rekap_' . str_replace(' ', '_', $this->trip->name) . '_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new SingleTripReportExport($this->trip), $fileName);
    }

    public function markAsCompleted(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }
        abort_if($this->trip->user_id !== $user->id, 403);

        $this->trip->update(['status' => 'completed']);
        $this->dispatch('notify', message: 'Trip telah ditandai Selesai.');
    }

    public function render(): View
    {
        $transactions = Transaction::where('trip_id', $this->trip->id)
            ->where('type', 'expense')
            ->with(['category', 'wallet'])
            ->orderBy('transaction_date', 'desc')
            ->get();

        $totalSpent = (float) $transactions->sum('amount');
        $surplusDeficit = (float) $this->trip->budget - $totalSpent;
        $isSurplus = $surplusDeficit >= 0;

        // Breakdown per category
        $categoryBreakdown = $transactions->groupBy(function ($tx) {
            return $tx->category ? $tx->category->name : 'Lain-lain';
        })->map(function ($items, $catName) use ($totalSpent) {
            $catTotal = (float) $items->sum('amount');
            $percentage = $totalSpent > 0 ? round(($catTotal / $totalSpent) * 100) : 0;
            $firstTx = $items->first();
            $color = $firstTx->category?->color ?? '#5865F2';

            return [
                'name' => $catName,
                'total' => $catTotal,
                'count' => $items->count(),
                'percentage' => $percentage,
                'color' => $color,
            ];
        })->sortByDesc('total');

        // Daily spending trend during the trip
        $dailySpending = $transactions->groupBy(function ($tx) {
            return $tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : 'Lainnya';
        })->map(function ($items, $dateKey) {
            return [
                'date' => $dateKey !== 'Lainnya' ? Carbon::parse($dateKey)->translatedFormat('d M') : 'Lainnya',
                'total' => (float) $items->sum('amount'),
                'count' => $items->count(),
            ];
        })->sortKeys();

        return view('livewire.trips.report', [
            'transactions' => $transactions,
            'totalSpent' => $totalSpent,
            'surplusDeficit' => $surplusDeficit,
            'isSurplus' => $isSurplus,
            'categoryBreakdown' => $categoryBreakdown,
            'dailySpending' => $dailySpending,
        ]);
    }
}
