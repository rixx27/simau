<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'destination_wallet_id',
        'trip_id',
        'category_id',
        'type', // expense, income, transfer
        'amount',
        'title',
        'note',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function destinationWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'destination_wallet_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->type === 'expense' ? '-Rp ' : '+Rp ';
        return $prefix . number_format($this->amount, 0, ',', '.');
    }

    public function getFormattedDateAttribute(): string
    {
        return Carbon::parse($this->transaction_date)->translatedFormat('d M Y');
    }

    public function getFormattedDateTimeAttribute(): string
    {
        return Carbon::parse($this->transaction_date)->translatedFormat('d M, H:i');
    }
}
