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
        $user = Auth::user() ?? User::first();
        if ($user && $trip->user_id !== $user->id) {
            abort(403);
        }
        $fileName = 'SIMAU_Rekap_' . str_replace(' ', '_', $trip->name) . '_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new SingleTripReportExport($trip), $fileName);
    })->name('trips.export');

    Route::get('/export/excel', function () {
        $user = Auth::user() ?? User::first();
        $fileName = 'SIMAU_Laporan_Keuangan_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new SimauReportExport($user), $fileName);
    })->name('export.excel');
});

require __DIR__.'/settings.php';
