<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'account_label',
        'type', // operational, trip, saving
        'current_balance',
        'target_amount',
        'daily_limit',
        'status', // active, completed
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'target_amount' => 'decimal:2',
        'daily_limit' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'wallet_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transaction::class, 'destination_wallet_id');
    }

    public function getFormattedBalanceAttribute(): string
    {
        return 'Rp ' . number_format($this->current_balance, 0, ',', '.');
    }

    public function getFormattedTargetAttribute(): string
    {
        return $this->target_amount ? 'Rp ' . number_format($this->target_amount, 0, ',', '.') : '-';
    }

    public function getFormattedDailyLimitAttribute(): string
    {
        return $this->daily_limit ? 'Rp ' . number_format($this->daily_limit, 0, ',', '.') : '-';
    }

    public function getProgressPercentageAttribute(): int
    {
        if (!$this->target_amount || $this->target_amount <= 0) {
            return 0;
        }

        $percentage = round(($this->current_balance / $this->target_amount) * 100);
        return min((int)$percentage, 100);
    }
}
