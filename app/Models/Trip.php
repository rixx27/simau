<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'destination',
        'start_date',
        'end_date',
        'budget',
        'status', // planned, active, completed
        'cover_image',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getFormattedBudgetAttribute(): string
    {
        return 'Rp ' . number_format($this->budget, 0, ',', '.');
    }

    public function getTotalSpentAttribute(): float
    {
        return (float) $this->transactions()->where('type', 'expense')->sum('amount');
    }

    public function getFormattedTotalSpentAttribute(): string
    {
        return 'Rp ' . number_format($this->total_spent, 0, ',', '.');
    }

    public function getRemainingBudgetAttribute(): float
    {
        return max(0, (float)$this->budget - $this->total_spent);
    }

    public function getFormattedRemainingBudgetAttribute(): string
    {
        return 'Rp ' . number_format($this->remaining_budget, 0, ',', '.');
    }

    public function getBudgetPercentageAttribute(): int
    {
        if ($this->budget <= 0) {
            return 0;
        }

        return (int) round(($this->total_spent / $this->budget) * 100);
    }

    public function getDaysDurationAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 1;
        }

        return max(1, $this->start_date->diffInDays($this->end_date) + 1);
    }

    public function getAverageDailySpentAttribute(): float
    {
        $days = $this->days_duration;
        return $days > 0 ? round($this->total_spent / $days) : 0;
    }

    public function getFormattedAverageDailySpentAttribute(): string
    {
        return 'Rp ' . number_format($this->average_daily_spent, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'planned' => 'Direncanakan',
            'active' => 'Sedang Berlangsung',
            'completed' => 'Selesai',
            default => ucfirst($this->status),
        };
    }

    public function getFormattedDateRangeAttribute(): string
    {
        if (!$this->start_date || !$this->end_date) {
            return '-';
        }

        return $this->start_date->translatedFormat('d M') . ' - ' . $this->end_date->translatedFormat('d M Y');
    }
}
