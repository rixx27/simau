<?php

use App\Exports\SimauReportExport;
use App\Exports\SingleTripReportExport;
use App\Http\Middleware\EnsureSimauUserAuthenticated;
use App\Livewire\Analytics\History;
use App\Livewire\Dashboard\Index;
use App\Livewire\Profile\Edit as ProfileEdit;
use App\Livewire\Trips\Index as TripsIndex;
use App\Livewire\Trips\Report as TripsReport;
use App\Livewire\Wallets\Index as WalletsIndex;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::middleware([EnsureSimauUserAuthenticated::class])->group(function () {
    Route::get('/', Index::class)->name('home');
    Route::get('/dashboard', Index::class)->name('dashboard');
    Route::get('/mutasi', History::class)->name('mutasi');
    Route::get('/trips', TripsIndex::class)->name('trips.index');
    Route::get('/trips/{trip}/rekap', TripsReport::class)->name('trips.report');
    Route::get('/tabungan', WalletsIndex::class)->name('tabungan');
    Route::get('/profile', ProfileEdit::class)->name('profile');

    Route::get('/trips/{trip}/export', function (Trip $trip) {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }
        abort_if($trip->user_id !== $user->id, 403);

        $fileName = 'SIMAU_Rekap_' . str_replace(' ', '_', $trip->name) . '_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new SingleTripReportExport($trip), $fileName);
    })->name('trips.export');

    Route::get('/export/excel', function (Request $request) {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $validated = $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        $month = isset($validated['month']) ? (int) $validated['month'] : null;
        $year = isset($validated['year']) ? (int) $validated['year'] : null;
        $fileName = 'SIMAU_Laporan_Keuangan_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new SimauReportExport($user, $month, $year), $fileName);
    })->name('export.excel');
});

require __DIR__.'/settings.php';
