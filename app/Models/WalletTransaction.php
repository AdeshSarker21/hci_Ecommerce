<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id', 'seller_id', 'type', 'amount',
        'balance_before', 'balance_after',
        'reference_type', 'reference_id',
        'description', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function reference()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        return $this->reference_type::find($this->reference_id);
    }

    public function getIsCreditAttribute(): bool
    {
        return str_contains($this->type, 'credit');
    }

    public function getIsDebitAttribute(): bool
    {
        return str_contains($this->type, 'debit');
    }

    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->is_credit ? '+' : '-';
        return $prefix . number_format(abs($this->amount), 2);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'commission_credit' => 'Commission Earned',
            'settlement_credit' => 'Settlement Received',
            'withdrawal_debit' => 'Withdrawal',
            'adjustment_credit' => 'Adjustment (Credit)',
            'adjustment_debit' => 'Adjustment (Debit)',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'commission_credit' => 'bg-green-100 text-green-800',
            'settlement_credit' => 'bg-blue-100 text-blue-800',
            'withdrawal_debit' => 'bg-red-100 text-red-800',
            'adjustment_credit' => 'bg-purple-100 text-purple-800',
            'adjustment_debit' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function scopeCredits($query)
    {
        return $query->where('type', 'like', '%credit');
    }

    public function scopeDebits($query)
    {
        return $query->where('type', 'like', '%debit');
    }
}
