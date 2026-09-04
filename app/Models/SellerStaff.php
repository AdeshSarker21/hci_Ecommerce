<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerStaff extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id', 'user_id', 'name', 'email', 'role',
        'can_manage_products', 'can_manage_orders',
        'can_manage_settings', 'can_view_reports',
        'is_active', 'notes', 'invited_at', 'joined_at', 'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'can_manage_products' => 'boolean',
            'can_manage_orders' => 'boolean',
            'can_manage_settings' => 'boolean',
            'can_view_reports' => 'boolean',
            'is_active' => 'boolean',
            'invited_at' => 'datetime',
            'joined_at' => 'datetime',
            'last_active_at' => 'datetime',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->is_active
            ? 'bg-green-100 text-green-800'
            : 'bg-red-100 text-red-800';
    }

    public function getPermissionsArray(): array
    {
        return array_filter([
            'products' => $this->can_manage_products,
            'orders' => $this->can_manage_orders,
            'settings' => $this->can_manage_settings,
            'reports' => $this->can_view_reports,
        ]);
    }
}
