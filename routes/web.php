<?php

use App\Exports\SimauReportExport;
use App\Http\Middleware\EnsureSimauUserAuthenticated;
use App\Livewire\Analytics\History;
use App\Livewire\Dashboard\Index;
use App\Livewire\Profile\Edit as ProfileEdit;
use App\Livewire\Wallets\Index as WalletsIndex;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::middleware([EnsureSimauUserAuthenticated::class])->group(function () {
    Route::get('/', Index::class)->name('home');
    Route::get('/dashboard', Index::class)->name('dashboard');
    Route::get('/mutasi', History::class)->name('mutasi');
    Route::get('/tabungan', WalletsIndex::class)->name('tabungan');
    Route::get('/profile', ProfileEdit::class)->name('profile');

    Route::get('/export/excel', function () {
        $user = Auth::user() ?? User::first();
        $fileName = 'SIMAU_Laporan_Keuangan_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new SimauReportExport($user), $fileName);
    })->name('export.excel');
});

require __DIR__.'/settings.php';
