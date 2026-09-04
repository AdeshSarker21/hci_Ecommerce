<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'name_bn', 'slug', 'logo', 'description',
        'website', 'status', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Brand $brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
            $baseSlug = $brand->slug;
            $counter = 1;
            while (static::withTrashed()->where('slug', $brand->slug)->exists()) {
                $brand->slug = $baseSlug . '-' . $counter;
                $counter++;
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff&size=200';
    }

    public function getFullNameAttribute(): string
    {
        return $this->name_bn ? "{$this->name} ({$this->name_bn})" : $this->name;
    }
}
