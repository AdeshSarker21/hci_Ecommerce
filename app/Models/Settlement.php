<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Settlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'settlement_number', 'seller_id', 'wallet_id',
        'amount', 'status', 'payment_method',
        'reference_number', 'notes',
        'processed_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Settlement $settlement) {
            if (empty($settlement->settlement_number)) {
                $settlement->settlement_number = self::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $prefix = 'STL';
        $date = now()->format('Ymd');
        do {
            $random = strtoupper(Str::random(6));
            $number = "{$prefix}-{$date}-{$random}";
        } while (static::where('settlement_number', $number)->exists());

        return $number;
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForSeller($query, int $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }
}
