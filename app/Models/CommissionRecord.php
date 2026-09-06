<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'seller_id', 'commission_rule_id',
        'order_amount', 'commission_amount', 'seller_earnings',
        'commission_type', 'commission_value',
        'status', 'currency', 'notes', 'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'order_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'seller_earnings' => 'decimal:2',
            'commission_value' => 'decimal:2',
            'settled_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function commissionRule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'settled' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFormattedOrderAmountAttribute(): string
    {
        return number_format($this->order_amount, 2);
    }

    public function getFormattedCommissionAttribute(): string
    {
        return number_format($this->commission_amount, 2);
    }

    public function getFormattedEarningsAttribute(): string
    {
        return number_format($this->seller_earnings, 2);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSettled($query)
    {
        return $query->where('status', 'settled');
    }

    public function scopeForSeller($query, int $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }
}
