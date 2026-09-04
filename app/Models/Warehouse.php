<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id', 'name', 'code', 'address', 'city', 'state',
        'country', 'postal_code', 'phone', 'email',
        'status', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_warehouse_stock')
            ->withPivot('quantity', 'reserved_quantity')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForSeller($query, ?int $sellerId)
    {
        if ($sellerId) {
            return $query->where('seller_id', $sellerId);
        }
        return $query->whereNull('seller_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFullAddressAttribute(): ?string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->country,
            $this->postal_code,
        ]);

        return $parts ? implode(', ', $parts) : null;
    }

    public function getTotalStockAttribute(): int
    {
        return $this->products()->sum('product_warehouse_stock.quantity');
    }
}
