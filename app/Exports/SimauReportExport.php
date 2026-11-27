<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SimauReportExport implements Export, WithMultipleSheets
{
    use Exportable;

    protected User $user;
    protected int $month;
    protected int $year;

    public function __construct(?User $user = null, ?int $month = null, ?int $year = null)
    {
        $resolvedUser = $user ?? auth()->user();
        if (!$resolvedUser) {
            abort(401);
        }

        $now = Carbon::now();
        $this->user = $resolvedUser;
        $this->month = $month ?? $now->month;
        $this->year = $year ?? $now->year;
    }

    public function sheets(): array
    {
        return [
            new SimauSummarySheet($this->user, $this->month, $this->year),
            new SimauMutationSheet($this->user, $this->month, $this->year),
        ];
    }
}
