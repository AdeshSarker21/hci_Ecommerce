<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'avatar', 'status', 'is_active', 'timezone', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id')
            ->withTimestamps();
    }

    public function apiTokens()
    {
        return $this->hasMany(ApiToken::class);
    }

    public function seller()
    {
        return $this->hasOne(Seller::class);
    }

    public function isSeller(): bool
    {
        return $this->hasRole('seller') || $this->seller()->exists();
    }

    public function hasSellerRole(): bool
    {
        return $this->hasAnyRole(['seller', 'seller-staff']);
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles()->where('slug', $slug)->exists();
    }

    public function hasAnyRole(array $slugs): bool
    {
        return $this->roles()->whereIn('slug', $slugs)->exists();
    }

    public function hasAllRoles(array $slugs): bool
    {
        $roleSlugs = $this->roles->pluck('slug')->toArray();
        return empty(array_diff($slugs, $roleSlugs));
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('slug')
            ->contains($slug);
    }

    public function hasAnyPermission(array $slugs): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('slug')
            ->intersect($slugs)
            ->isNotEmpty();
    }

    public function hasAllPermissions(array $slugs): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        $permissionSlugs = $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('slug')
            ->toArray();

        return empty(array_diff($slugs, $permissionSlugs));
    }

    public function isActive(): bool
    {
        return $this->is_active && $this->status === 'active';
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            return $this->avatar;
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
    }
}
