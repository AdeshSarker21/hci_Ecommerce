<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id', 'category_id', 'brand_id',
        'name', 'name_bn', 'slug', 'description', 'description_bn',
        'type', 'sku',
        'price', 'compare_at_price', 'cost_price',
        'quantity', 'manage_stock', 'low_stock_threshold',
        'status', 'rejection_reason', 'published_at', 'approved_at',
        'is_featured', 'is_active',
        'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'quantity' => 'integer',
            'manage_stock' => 'boolean',
            'low_stock_threshold' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'published_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            $baseSlug = $product->slug;
            $counter = 1;
            while (static::withTrashed()->where('slug', $product->slug)->exists()) {
                $product->slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            if (empty($product->sku)) {
                $product->sku = 'SKU-' . strtoupper(uniqid());
            }
        });
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'published');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySeller($query, int $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPendingReview(): bool
    {
        return $this->status === 'pending_review';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function submitForReview(): bool
    {
        return $this->update(['status' => 'pending_review']);
    }

    public function approve(?string $reason = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject(?string $reason = null): bool
    {
        return $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    public function publish(): bool
    {
        return $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function unpublish(): bool
    {
        return $this->update([
            'status' => 'approved',
            'published_at' => null,
        ]);
    }

    public function inStock(): bool
    {
        if (!$this->manage_stock) {
            return true;
        }
        return $this->quantity > 0;
    }

    public function isLowStock(): bool
    {
        if (!$this->manage_stock) {
            return false;
        }
        return $this->quantity <= $this->low_stock_threshold;
    }

    public function getDiscountPercentageAttribute(): ?float
    {
        if (!$this->compare_at_price || $this->compare_at_price <= $this->price) {
            return null;
        }
        return round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100, 1);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'pending_review' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'published' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'physical' => 'bg-indigo-100 text-indigo-800',
            'digital' => 'bg-purple-100 text-purple-800',
            'service' => 'bg-teal-100 text-teal-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStockBadgeAttribute(): string
    {
        if (!$this->manage_stock) {
            return 'bg-gray-100 text-gray-800';
        }
        if ($this->quantity <= 0) {
            return 'bg-red-100 text-red-800';
        }
        if ($this->isLowStock()) {
            return 'bg-yellow-100 text-yellow-800';
        }
        return 'bg-green-100 text-green-800';
    }

    public function getStockLabelAttribute(): string
    {
        if (!$this->manage_stock) {
            return 'Unlimited';
        }
        if ($this->quantity <= 0) {
            return 'Out of Stock';
        }
        if ($this->isLowStock()) {
            return 'Low Stock (' . $this->quantity . ')';
        }
        return 'In Stock (' . $this->quantity . ')';
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function getFullNameAttribute(): string
    {
        return $this->name_bn ? "{$this->name} ({$this->name_bn})" : $this->name;
    }
}
