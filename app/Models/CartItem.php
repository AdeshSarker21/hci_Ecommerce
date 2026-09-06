<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'quantity',
        'selected_variants',
        'price',
        'compare_at_price',
    ];

    protected function casts(): array
    {
        return [
            'selected_variants' => 'array',
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    public function getDiscountAmountAttribute(): float
    {
        if (!$this->compare_at_price || $this->compare_at_price <= $this->price) {
            return 0;
        }
        return ($this->compare_at_price - $this->price) * $this->quantity;
    }

    public function getSellerAttribute(): ?Seller
    {
        return $this->product?->seller;
    }

    public function getCategoryNameAttribute(): ?string
    {
        return $this->product?->category?->name;
    }

    public function getBrandNameAttribute(): ?string
    {
        return $this->product?->brand?->name;
    }
}
