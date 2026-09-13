<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierWebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'courier_id', 'shipment_id', 'event_type', 'courier_slug',
        'consignment_id', 'payload', 'headers', 'ip_address',
        'idempotency_key', 'is_processed', 'processing_result',
        'error_message', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'headers' => 'array',
            'is_processed' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function markProcessed(?string $result = null): void
    {
        $this->update([
            'is_processed' => true,
            'processing_result' => $result,
            'processed_at' => now(),
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'is_processed' => false,
            'error_message' => $error,
            'processed_at' => now(),
        ]);
    }

    public static function isDuplicate(string $idempotencyKey): bool
    {
        return static::where('idempotency_key', $idempotencyKey)
            ->where('is_processed', true)
            ->exists();
    }
}
