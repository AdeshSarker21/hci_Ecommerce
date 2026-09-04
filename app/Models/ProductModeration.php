<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductModeration extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'reviewer_id',
        'action',
        'reason',
        'previous_status',
        'new_status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public const ACTIONS = [
        'submit_for_review',
        'approve',
        'reject',
        'publish',
        'unpublish',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'submit_for_review' => 'Submitted for Review',
            'approve' => 'Approved',
            'reject' => 'Rejected',
            'publish' => 'Published',
            'unpublish' => 'Unpublished',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    public function getActionBadgeAttribute(): string
    {
        return match ($this->action) {
            'submit_for_review' => 'bg-yellow-100 text-yellow-800',
            'approve' => 'bg-green-100 text-green-800',
            'reject' => 'bg-red-100 text-red-800',
            'publish' => 'bg-blue-100 text-blue-800',
            'unpublish' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
