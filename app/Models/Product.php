<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id', 'category_id', 'brand_id',
        'name', 'name_bn', 'slug', 'description', 'description_bn',
        'type', 'sku', 'barcode',
        'price', 'compare_at_price', 'cost_price',
        'quantity', 'reserved_quantity', 'manage_stock', 'low_stock_threshold',
        'status', 'rejection_reason', 'published_at', 'approved_at',
        'is_featured', 'is_active',
        'meta_title', 'meta_description',
        'weight', 'length', 'width', 'height', 'shipping_class',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'manage_stock' => 'boolean',
            'low_stock_threshold' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'published_at' => 'datetime',
            'approved_at' => 'datetime',
            'weight' => 'decimal:2',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
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
                $product->sku = self::generateUniqueSku();
            }
            if (empty($product->barcode) && $product->sku) {
                $product->barcode = $product->sku;
            }
        });
    }

    public static function generateUniqueSku(): string
    {
        $prefix = 'SKU';
        do {
            $number = str_pad(Cache::increment('sku_counter'), 6, '0', STR_PAD_LEFT);
            $sku = $prefix . '-' . $number;
        } while (static::withTrashed()->where('sku', $sku)->exists());

        return $sku;
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

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot(['quantity', 'unit_price', 'total'])
            ->withTimestamps();
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse_stock')
            ->withPivot('quantity', 'reserved_quantity')
            ->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function moderations(): HasMany
    {
        return $this->hasMany(ProductModeration::class)->latest();
    }

    public function logModeration(string $action, ?string $reason, string $previousStatus, string $newStatus, ?int $reviewerId = null): ProductModeration
    {
        return $this->moderations()->create([
            'reviewer_id' => $reviewerId ?? auth()->id(),
            'action' => $action,
            'reason' => $reason,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
        ]);
    }

    public function getWarehouseStock(?int $warehouseId = null): int
    {
        if ($warehouseId) {
            $stock = $this->warehouses()->where('warehouse_id', $warehouseId)->first()?->pivot;
            return $stock ? $stock->quantity : 0;
        }
        return $this->warehouses()->sum('product_warehouse_stock.quantity');
    }

    public function getAvailableStockAttribute(): int
    {
        if (!$this->manage_stock) {
            return PHP_INT_MAX;
        }
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    public function getBarcodeUrlAttribute(): ?string
    {
        if (!$this->sku) {
            return null;
        }
        $path = 'barcodes/' . $this->sku . '.svg';
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }
        return null;
    }

    public function getQrCodeUrlAttribute(): ?string
    {
        if (!$this->sku) {
            return null;
        }
        $path = 'qr-codes/' . $this->sku . '.svg';
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }
        return null;
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

    public function getPrimaryImageAttribute(): ?string
    {
        $featured = $this->images()->where('is_featured', true)->first();
        if ($featured) {
            return $featured->path;
        }
        return $this->images()->first()?->path;
    }
}
