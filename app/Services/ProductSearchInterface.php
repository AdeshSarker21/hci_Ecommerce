<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductSearchInterface
{
    public function search(array $filters): LengthAwarePaginator;

    public function indexProduct(object $product): void;

    public function removeProduct(int $productId): void;

    public function reindexAll(): int;
}
