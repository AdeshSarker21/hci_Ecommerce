<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'product_id', 'type', 'quantity',
        'quantity_before', 'quantity_after',
        'reference_type', 'reference_id',
        'notes', 'created_by_type', 'created_by_id',
        'unit_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'quantity_before' => 'integer',
            'quantity_after' => 'integer',
            'unit_cost' => 'decimal:2',
        ];
    }

    public const TYPES = [
        'sale' => 'Sale',
        'return' => 'Return',
        'cancellation' => 'Cancellation',
        'adjustment' => 'Adjustment',
        'restock' => 'Restock',
        'damaged' => 'Damaged',
        'lost' => 'Lost',
        'reservation' => 'Reservation',
        'reservation_release' => 'Reservation Released',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'sale' => 'bg-red-100 text-red-800',
            'return' => 'bg-green-100 text-green-800',
            'cancellation' => 'bg-yellow-100 text-yellow-800',
            'adjustment' => 'bg-blue-100 text-blue-800',
            'restock' => 'bg-emerald-100 text-emerald-800',
            'damaged' => 'bg-orange-100 text-orange-800',
            'lost' => 'bg-gray-100 text-gray-800',
            'reservation' => 'bg-purple-100 text-purple-800',
            'reservation_release' => 'bg-indigo-100 text-indigo-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getQuantityDirectionAttribute(): string
    {
        return $this->quantity > 0 ? '+' : '';
    }
}
