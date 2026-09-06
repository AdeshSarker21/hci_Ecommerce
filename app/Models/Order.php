<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number', 'seller_id', 'user_id', 'status',
        'subtotal', 'tax', 'shipping_cost', 'discount', 'total',
        'currency', 'notes', 'seller_notes', 'shipping_address', 'billing_address',
        'payment_method', 'payment_status',
        'tracking_number', 'courier_name', 'tracking_url',
        'paid_at', 'processing_at', 'shipped_at', 'delivered_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'paid_at' => 'datetime',
            'processing_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        do {
            $random = strtoupper(Str::random(6));
            $orderNumber = "{$prefix}-{$date}-{$random}";
        } while (static::withTrashed()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;

        return $query->where(function ($q) use ($search) {
            $q->where('order_number', 'like', "%{$search}%")
              ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
              ->orWhereHas('items', fn ($iq) => $iq->where('product_name', 'like', "%{$search}%")->orWhere('product_sku', 'like', "%{$search}%"));
        });
    }

    public function logStatusChange(string $toStatus, ?string $fromStatus = null, ?int $userId = null, ?string $note = null, string $type = 'status'): OrderStatusHistory
    {
        return $this->statusHistory()->create([
            'user_id' => $userId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'type' => $type,
            'note' => $note,
        ]);
    }

    public function getAllowedStatusTransitions(): array
    {
        return match ($this->status) {
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
            default => [],
        };
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['delivered', 'completed']);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeForSeller($query, int $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isShipped(): bool
    {
        return $this->status === 'shipped';
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'shipped' => 'bg-indigo-100 text-indigo-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'refunded' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'paid' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'refunded' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2);
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items()->sum('quantity');
    }

    public function getCustomerNameAttribute(): string
    {
        return $this->user?->name ?? 'Guest';
    }
}
