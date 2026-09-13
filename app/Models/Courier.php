<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Courier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'code', 'description', 'logo', 'website',
        'api_key', 'api_secret', 'access_token', 'token_expires_at',
        'api_base_url', 'api_config', 'webhook_config', 'webhook_secret',
        'supported_services',
        'is_active', 'is_default', 'cod_support', 'tracking_support',
        'shipment_creation_support', 'return_support',
        'cod_fee', 'cod_percentage',
        'weight_limit_kg', 'max_value', 'supported_areas', 'config',
        'sort_order', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'api_config' => 'array',
            'webhook_config' => 'array',
            'supported_services' => 'array',
            'supported_areas' => 'array',
            'config' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'cod_support' => 'boolean',
            'tracking_support' => 'boolean',
            'shipment_creation_support' => 'boolean',
            'return_support' => 'boolean',
            'cod_fee' => 'decimal:2',
            'cod_percentage' => 'decimal:2',
            'weight_limit_kg' => 'decimal:2',
            'max_value' => 'decimal:2',
            'token_expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Courier $courier) {
            if ($courier->is_default && $courier->wasChanged('is_default') && $courier->is_default) {
                static::where('id', '!=', $courier->id)->update(['is_default' => false]);
            }
        });
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function webhookLogs()
    {
        return $this->hasMany(CourierWebhookLog::class);
    }

    public function collections()
    {
        return $this->hasMany(CourierCollection::class, 'courier_name', 'slug');
    }

    public function setApiKeyAttribute($value): void
    {
        $this->attributes['api_key'] = $value ? encrypt($value) : null;
    }

    public function getApiKeyDecryptedAttribute(): ?string
    {
        try {
            return $this->api_key ? decrypt($this->api_key) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function setApiSecretAttribute($value): void
    {
        $this->attributes['api_secret'] = $value ? encrypt($value) : null;
    }

    public function getApiSecretDecryptedAttribute(): ?string
    {
        try {
            return $this->api_secret ? decrypt($this->api_secret) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function setAccessTokenAttribute($value): void
    {
        $this->attributes['access_token'] = $value ? encrypt($value) : null;
    }

    public function getAccessTokenDecryptedAttribute(): ?string
    {
        try {
            return $this->access_token ? decrypt($this->access_token) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function setWebhookSecretAttribute($value): void
    {
        $this->attributes['webhook_secret'] = $value ? encrypt($value) : null;
    }

    public function getWebhookSecretDecryptedAttribute(): ?string
    {
        try {
            return $this->webhook_secret ? decrypt($this->webhook_secret) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    public function getApiConnectionStatusAttribute(): string
    {
        if (!$this->api_key) return 'not_configured';
        if ($this->token_expires_at && $this->token_expires_at->isPast()) return 'token_expired';
        if ($this->last_synced_at && $this->last_synced_at->greaterThan(now()->subHours(24))) return 'connected';
        return 'configured';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForApi($query)
    {
        return $query->where('is_active', true)->orderByDesc('is_default')->orderBy('sort_order');
    }

    public static function getDefault(): ?static
    {
        return static::where('is_default', true)->where('is_active', true)->first()
            ?? static::where('is_active', true)->orderBy('sort_order')->first();
    }

    public static function getBySlug(string $slug): ?static
    {
        return static::where('slug', $slug)->where('is_active', true)->first();
    }

    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    public function getWebhookUrlAttribute(): string
    {
        return url("/api/webhooks/courier/{$this->slug}");
    }

    public function getSupportsAttribute(): array
    {
        return [
            'cod' => $this->cod_support,
            'tracking' => $this->tracking_support,
            'shipment_creation' => $this->shipment_creation_support,
            'return' => $this->return_support,
        ];
    }
}
