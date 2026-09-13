<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'seller_id', 'courier_id', 'commission_record_id',
        'courier_name', 'consignment_id',
        'cod_amount', 'courier_charge', 'net_collected', 'marketplace_earning',
        'commission_amount', 'seller_earning',
        'collection_status', 'settlement_status', 'collected_at', 'confirmed_at',
        'delivery_date', 'reference_number', 'notes', 'admin_notes',
        'reconciliation_status', 'reconciliation_reference', 'reconciled_at', 'reconciled_by',
        'courier_payment_reference', 'courier_payment_date',
        'discrepancy_amount', 'discrepancy_notes',
        'import_batch_id', 'raw_courier_data',
    ];

    protected function casts(): array
    {
        return [
            'cod_amount' => 'decimal:2',
            'courier_charge' => 'decimal:2',
            'net_collected' => 'decimal:2',
            'marketplace_earning' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'seller_earning' => 'decimal:2',
            'discrepancy_amount' => 'decimal:2',
            'raw_courier_data' => 'array',
            'collected_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'delivery_date' => 'datetime',
            'reconciled_at' => 'datetime',
            'courier_payment_date' => 'datetime',
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

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function commissionRecord(): BelongsTo
    {
        return $this->belongsTo(CommissionRecord::class);
    }

    public function reconciledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'consignment_id', 'consignment_id');
    }

    public function scopePending($query)
    {
        return $query->where('collection_status', 'pending');
    }

    public function scopeCollected($query)
    {
        return $query->where('collection_status', 'collected');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('collection_status', 'confirmed');
    }

    public function scopeForSeller($query, int $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    public function scopeSettlementPending($query)
    {
        return $query->where('settlement_status', 'pending');
    }

    public function scopeSettled($query)
    {
        return $query->where('settlement_status', 'settled');
    }

    public function scopeReconciled($query)
    {
        return $query->where('reconciliation_status', 'reconciled');
    }

    public function scopeUnmatched($query)
    {
        return $query->where('reconciliation_status', 'pending');
    }

    public function scopeHasDiscrepancy($query)
    {
        return $query->where('reconciliation_status', 'discrepancy');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->collection_status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'collected' => 'bg-blue-100 text-blue-800',
            'confirmed' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getSettlementStatusBadgeAttribute(): string
    {
        return match ($this->settlement_status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'settled' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getReconciliationStatusBadgeAttribute(): string
    {
        return match ($this->reconciliation_status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'reconciled' => 'bg-green-100 text-green-800',
            'discrepancy' => 'bg-orange-100 text-orange-800',
            'manual_review' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFormattedCodAmountAttribute(): string
    {
        return number_format($this->cod_amount, 2);
    }

    public function getFormattedSellerEarningAttribute(): string
    {
        return number_format($this->seller_earning, 2);
    }

    public function getNetCollectedAttribute(): float
    {
        return $this->cod_amount - $this->courier_charge;
    }

    public function isReconciled(): bool
    {
        return $this->reconciliation_status === 'reconciled';
    }

    public function hasDiscrepancy(): bool
    {
        return $this->reconciliation_status === 'discrepancy';
    }

    public function canBeReconciled(): bool
    {
        return in_array($this->reconciliation_status, ['pending', 'discrepancy', 'manual_review']);
    }
}
