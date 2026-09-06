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
        'seller_id', 'pending_balance', 'available_balance',
        'withdrawn_amount', 'total_earned', 'currency',
    ];

    protected function casts(): array
    {
        return [
            'pending_balance' => 'decimal:2',
            'available_balance' => 'decimal:2',
            'withdrawn_amount' => 'decimal:2',
            'total_earned' => 'decimal:2',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    public function getFormattedPendingAttribute(): string
    {
        return number_format($this->pending_balance, 2);
    }

    public function getFormattedAvailableAttribute(): string
    {
        return number_format($this->available_balance, 2);
    }

    public function getFormattedWithdrawnAttribute(): string
    {
        return number_format($this->withdrawn_amount, 2);
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_earned, 2);
    }

    public function getBalanceAttribute(): float
    {
        return (float) $this->pending_balance + (float) $this->available_balance;
    }
}
