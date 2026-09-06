<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'order_status_history';

    protected $fillable = [
        'order_id', 'user_id', 'from_status', 'to_status',
        'type', 'note', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'status' => 'bg-blue-100 text-blue-800',
            'payment' => 'bg-green-100 text-green-800',
            'shipment' => 'bg-indigo-100 text-indigo-800',
            'note' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->to_status) {
            'pending' => 'yellow',
            'processing' => 'blue',
            'shipped' => 'indigo',
            'delivered' => 'green',
            'cancelled' => 'red',
            'refunded' => 'purple',
            'paid' => 'green',
            'failed' => 'red',
            default => 'gray',
        };
    }
}
