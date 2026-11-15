<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SimauReportExport implements Export, WithMultipleSheets
{
    use Exportable;

    protected ?User $user;
    protected ?int $month;
    protected ?int $year;

    public function __construct(?User $user = null, ?int $month = null, ?int $year = null)
    {
        $this->user = $user ?? User::first();
        $this->month = $month ?? 11;
        $this->year = $year ?? 2026;
    }

    public function sheets(): array
    {
        return [
            new SimauSummarySheet($this->user, $this->month, $this->year),
            new SimauMutationSheet($this->user, $this->month, $this->year),
        ];
    }
}
