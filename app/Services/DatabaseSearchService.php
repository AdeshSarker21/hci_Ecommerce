<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DatabaseSearchService implements ProductSearchInterface
{
    public function search(array $filters): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['seller', 'category', 'brand'])
            ->where('status', 'published')
            ->where('is_active', true);

        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('name_bn', 'like', "%{$keyword}%")
                  ->orWhere('sku', 'like', "%{$keyword}%")
                  ->orWhere('barcode', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhere('description_bn', 'like', "%{$keyword}%")
                  ->orWhereHas('brand', fn ($bq) => $bq->where('name', 'like', "%{$keyword}%"));
            });
        }

        if (!empty($filters['category_id'])) {
            $categoryIds = is_array($filters['category_id'])
                ? $filters['category_id']
                : [$filters['category_id']];
            $query->whereIn('category_id', $categoryIds);
        }

        if (!empty($filters['brand_id'])) {
            $brandIds = is_array($filters['brand_id'])
                ? $filters['brand_id']
                : [$filters['brand_id']];
            $query->whereIn('brand_id', $brandIds);
        }

        if (!empty($filters['seller_id'])) {
            $query->where('seller_id', $filters['seller_id']);
        }

        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['in_stock'])) {
            $query->where(function (Builder $q) {
                $q->where('manage_stock', false)
                  ->orWhereColumn('quantity', '>', 'reserved_quantity');
            });
        }

        if (!empty($filters['is_featured'])) {
            $query->where('is_featured', true);
        }

        if (!empty($filters['attribute_values'])) {
            foreach ($filters['attribute_values'] as $attributeId => $value) {
                $query->whereHas('attributeValues', function (Builder $avq) use ($attributeId, $value) {
                    $avq->where('attribute_id', $attributeId)
                        ->where('value', $value);
                });
            }
        }

        $sort = $filters['sort'] ?? 'newest';
        $query = $this->applySorting($query, $sort);

        $perPage = $filters['per_page'] ?? 24;

        return $query->paginate($perPage)->withQueryString();
    }

    private function applySorting(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'newest' => $query->latest(),
            'oldest' => $query->oldest(),
            'rating' => $query->join('sellers', 'products.seller_id', '=', 'sellers.id')
                ->orderByDesc('sellers.average_rating')
                ->select('products.*'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'popularity' => $query->withCount('orders as sales_count')
                ->orderByDesc('sales_count')
                ->latest(),
            default => $query->latest(),
        };
    }

    public function indexProduct(object $product): void
    {
        // Database search - no-op, products are searchable via query
    }

    public function removeProduct(int $productId): void
    {
        // Database search - no-op
    }

    public function reindexAll(): int
    {
        // Database search - no-op
        return Product::where('status', 'published')->count();
    }
}
