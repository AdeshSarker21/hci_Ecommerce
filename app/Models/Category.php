<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id', 'name', 'name_bn', 'slug', 'description',
        'image', 'status', 'sort_order', 'depth', 'path',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'depth' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
            $baseSlug = $category->slug;
            $counter = 1;
            while (static::withTrashed()->where('slug', $category->slug)->exists()) {
                $category->slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            if ($category->parent_id) {
                $category->depth = $category->parent->depth + 1;
                $category->path = ($category->parent->path ? $category->parent->path . '/' : '') . $category->parent_id;
            } else {
                $category->depth = 0;
                $category->path = '';
            }
        });

        static::updating(function (Category $category) {
            if ($category->parent_id && !$category->wasChanged('parent_id')) {
                return;
            }
            if ($category->parent_id) {
                $parent = Category::find($category->parent_id);
                if ($parent) {
                    $category->depth = $parent->depth + 1;
                    $category->path = ($parent->path ? $parent->path . '/' : '') . $parent->id;
                }
            } else {
                $category->depth = 0;
                $category->path = '';
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
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

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function getAncestors()
    {
        if (!$this->path) {
            return collect();
        }

        $ids = explode('/', $this->path);

        return static::whereIn('id', $ids)->orderBy('depth')->get();
    }

    public function getBreadcrumbsAttribute(): string
    {
        $ancestors = $this->getAncestors();
        $crumbs = $ancestors->pluck('name')->toArray();
        $crumbs[] = $this->name;

        return implode(' > ', $crumbs);
    }

    public function getChildrenCountAttribute(): int
    {
        return $this->children()->count();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ?? null;
    }

    public function getFullNameAttribute(): string
    {
        return $this->name_bn ? "{$this->name} ({$this->name_bn})" : $this->name;
    }

    public function delete(): bool
    {
        $this->children()->update(['parent_id' => $this->parent_id]);

        return parent::delete();
    }
}
