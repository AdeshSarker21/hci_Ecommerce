<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id', 'value', 'value_bn', 'slug',
        'color_code', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AttributeValue $value) {
            if (empty($value->slug)) {
                $value->slug = Str::slug($value->value);
            }
        });
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->value_bn ? "{$this->value} ({$this->value_bn})" : $this->value;
    }
}
