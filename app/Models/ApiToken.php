<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiToken extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'token', 'name', 'abilities', 'last_used_at', 'expires_at'];

    protected function casts(): array
    {
        return [
            'abilities' => 'array',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function can(string $ability): bool
    {
        if (in_array('*', $this->abilities ?? [])) {
            return true;
        }

        return in_array($ability, $this->abilities ?? []);
    }

    public function markUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
