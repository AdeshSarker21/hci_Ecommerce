<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Seller extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'store_name', 'store_slug', 'store_description',
        'store_logo', 'store_banner', 'contact_email', 'contact_phone',
        'contact_website', 'business_address', 'business_city',
        'business_state', 'business_country', 'business_postal_code',
        'business_registration_number', 'tax_id', 'status',
        'rejection_reason', 'commission_rate', 'is_featured',
        'is_active', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Seller $seller) {
            if (empty($seller->store_slug)) {
                $seller->store_slug = Str::slug($seller->store_name);
            }
            if (empty($seller->contact_email) && $seller->user) {
                $seller->contact_email = $seller->user->email;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'seller_staff', 'seller_id', 'user_id')
            ->withPivot([
                'role', 'can_manage_products', 'can_manage_orders',
                'can_manage_settings', 'can_view_reports',
                'invited_at', 'joined_at',
            ])
            ->withTimestamps();
    }

    public function staffDetails(): HasMany
    {
        return $this->hasMany(SellerStaff::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function approve(): bool
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

    public function suspend(?string $reason = null): bool
    {
        return $this->update([
            'status' => 'suspended',
            'rejection_reason' => $reason,
        ]);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->store_logo ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->store_name) . '&background=10b981&color=fff&size=200';
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->store_banner ?? null;
    }

    public function getFullAddressAttribute(): ?string
    {
        $parts = array_filter([
            $this->business_address,
            $this->business_city,
            $this->business_state,
            $this->business_country,
            $this->business_postal_code,
        ]);

        return $parts ? implode(', ', $parts) : null;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'suspended' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
