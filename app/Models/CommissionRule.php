<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'value', 'applies_to',
        'seller_id', 'category_id', 'product_id',
        'is_active', 'priority', 'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function calculateCommission(float $amount): float
    {
        return match ($this->type) {
            'percentage' => round($amount * ($this->value / 100), 2),
            'fixed' => min($this->value, $amount),
            default => 0,
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'percentage' => 'bg-blue-100 text-blue-800',
            'fixed' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getAppliesToLabelAttribute(): string
    {
        return match ($this->applies_to) {
            'global' => 'All Sellers',
            'seller' => $this->seller?->store_name ?? 'Specific Seller',
            'category' => $this->category?->name ?? 'Specific Category',
            'product' => $this->product?->name ?? 'Specific Product',
            default => ucfirst($this->applies_to),
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSeller($query, int $sellerId)
    {
        return $query->where(function ($q) use ($sellerId) {
            $q->where('applies_to', 'global')
              ->orWhere(function ($sq) use ($sellerId) {
                  $sq->where('applies_to', 'seller')->where('seller_id', $sellerId);
              });
        });
    }
}
