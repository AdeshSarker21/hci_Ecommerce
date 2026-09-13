<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'courier_id', 'seller_id',
        'shipment_number', 'consignment_id', 'tracking_code', 'invoice_number',
        'status', 'internal_status', 'previous_status', 'status_details', 'status_history',
        'recipient_name', 'recipient_phone', 'recipient_address',
        'recipient_city', 'recipient_area', 'recipient_zone',
        'cod_amount', 'shipping_fee', 'cod_fee', 'total_collectable',
        'total_delivered', 'total_returned', 'weight_kg', 'quantity',
        'courier_name', 'courier_status', 'courier_response', 'courier_data',
        'pickup_address', 'pickup_area', 'delivery_type', 'note',
        'admin_notes', 'error_message',
        'pickup_date', 'shipped_at', 'in_transit_at', 'out_for_delivery_at',
        'delivered_at', 'returned_at', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'status_history' => 'array',
            'courier_response' => 'array',
            'courier_data' => 'array',
            'cod_amount' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'cod_fee' => 'decimal:2',
            'total_collectable' => 'decimal:2',
            'total_delivered' => 'decimal:2',
            'total_returned' => 'decimal:2',
            'weight_kg' => 'decimal:2',
            'quantity' => 'integer',
            'pickup_date' => 'datetime',
            'shipped_at' => 'datetime',
            'in_transit_at' => 'datetime',
            'out_for_delivery_at' => 'datetime',
            'delivered_at' => 'datetime',
            'returned_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Shipment $shipment) {
            if (empty($shipment->shipment_number)) {
                $shipment->shipment_number = self::generateShipmentNumber();
            }
            if (empty($shipment->internal_status)) {
                $shipment->internal_status = 'pending';
            }
        });
    }

    public static function generateShipmentNumber(): string
    {
        $prefix = 'SHP';
        $date = now()->format('Ymd');
        do {
            $random = strtoupper(Str::random(6));
            $number = "{$prefix}-{$date}-{$random}";
        } while (static::where('shipment_number', $number)->exists());
        return $number;
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function webhookLogs()
    {
        return $this->hasMany(CourierWebhookLog::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->internal_status ?? $this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'pickup_requested' => 'bg-cyan-100 text-cyan-800',
            'picked_up' => 'bg-blue-100 text-blue-800',
            'in_transit' => 'bg-indigo-100 text-indigo-800',
            'out_for_delivery' => 'bg-purple-100 text-purple-800',
            'delivered' => 'bg-green-100 text-green-800',
            'partial_delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-gray-100 text-gray-600',
            'on_hold' => 'bg-orange-100 text-orange-800',
            'return_requested' => 'bg-red-100 text-red-600',
            'returned' => 'bg-red-100 text-red-800',
            'delivery_failed' => 'bg-red-100 text-red-800',
            'failed' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->internal_status ?? $this->status]
            ?? ucfirst(str_replace('_', ' ', $this->internal_status ?? $this->status));
    }

    public static function statuses(): array
    {
        return [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'pickup_requested' => 'Pickup Requested',
            'picked_up' => 'Picked Up',
            'in_transit' => 'In Transit',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'partial_delivered' => 'Partial Delivered',
            'cancelled' => 'Cancelled',
            'on_hold' => 'On Hold',
            'return_requested' => 'Return Requested',
            'returned' => 'Returned',
            'delivery_failed' => 'Delivery Failed',
            'failed' => 'Failed',
            'unknown' => 'Unknown',
        ];
    }

    public function updateStatus(string $newStatus, ?string $details = null, ?array $courierData = null): bool
    {
        $oldStatus = $this->internal_status ?? $this->status;

        $history = $this->status_history ?? [];
        $history[] = [
            'from' => $oldStatus,
            'to' => $newStatus,
            'details' => $details,
            'courier_data' => $courierData,
            'timestamp' => now()->toISOString(),
        ];

        $updateData = [
            'status' => $newStatus,
            'internal_status' => $newStatus,
            'previous_status' => $oldStatus,
            'status_details' => $details,
            'status_history' => $history,
            'courier_response' => $courierData,
            'last_synced_at' => now(),
        ];

        match ($newStatus) {
            'picked_up' => $updateData['shipped_at'] = now(),
            'in_transit' => $updateData['in_transit_at'] = now(),
            'out_for_delivery' => $updateData['out_for_delivery_at'] = now(),
            'delivered' => $updateData['delivered_at'] = now(),
            'returned' => $updateData['returned_at'] = now(),
            default => null,
        };

        return $this->update($updateData);
    }

    public function scopePending($query)
    {
        return $query->where('internal_status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('internal_status', ['delivered', 'returned', 'cancelled', 'failed']);
    }

    public function scopeForSeller($query, int $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }
}
