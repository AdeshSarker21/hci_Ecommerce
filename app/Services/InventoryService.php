<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function addStock(Product $product, int $quantity, ?string $notes = null, ?string $unitCost = null, ?string $referenceType = null, ?int $referenceId = null, $createdBy = null): InventoryTransaction
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Stock quantity must be positive.');
        }

        return DB::transaction(function () use ($product, $quantity, $notes, $unitCost, $referenceType, $referenceId, $createdBy) {
            $locked = Product::lockForUpdate()->find($product->id);
            $before = $locked->quantity;

            $locked->increment('quantity', $quantity);

            return InventoryTransaction::create([
                'product_id' => $product->id,
                'type' => 'restock',
                'quantity' => $quantity,
                'quantity_before' => $before,
                'quantity_after' => $before + $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_by_type' => $createdBy ? get_class($createdBy) : null,
                'created_by_id' => $createdBy?->id,
                'unit_cost' => $unitCost,
            ]);
        });
    }

    public function removeStock(Product $product, int $quantity, string $type = 'sale', ?string $notes = null, ?string $referenceType = null, ?int $referenceId = null, $createdBy = null): InventoryTransaction
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Stock quantity must be positive.');
        }

        return DB::transaction(function () use ($product, $quantity, $type, $notes, $referenceType, $referenceId, $createdBy) {
            $locked = Product::lockForUpdate()->find($product->id);

            if (!$locked->manage_stock) {
                throw new \RuntimeException('Product does not manage stock.');
            }

            if ($locked->quantity < $quantity) {
                throw new \RuntimeException('Insufficient stock. Available: ' . $locked->quantity . ', requested: ' . $quantity);
            }

            $before = $locked->quantity;
            $locked->decrement('quantity', $quantity);

            return InventoryTransaction::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => -$quantity,
                'quantity_before' => $before,
                'quantity_after' => $before - $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_by_type' => $createdBy ? get_class($createdBy) : null,
                'created_by_id' => $createdBy?->id,
            ]);
        });
    }

    public function adjustStock(Product $product, int $newQuantity, ?string $notes = null, $createdBy = null): InventoryTransaction
    {
        return DB::transaction(function () use ($product, $newQuantity, $notes, $createdBy) {
            $locked = Product::lockForUpdate()->find($product->id);
            $before = $locked->quantity;
            $difference = $newQuantity - $before;

            $locked->update(['quantity' => $newQuantity]);

            return InventoryTransaction::create([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity' => $difference,
                'quantity_before' => $before,
                'quantity_after' => $newQuantity,
                'notes' => $notes ?? "Adjusted from {$before} to {$newQuantity}",
                'created_by_type' => $createdBy ? get_class($createdBy) : null,
                'created_by_id' => $createdBy?->id,
            ]);
        });
    }

    public function reserveStock(Product $product, int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        return DB::transaction(function () use ($product, $quantity) {
            $locked = Product::lockForUpdate()->find($product->id);

            if (!$locked->manage_stock) {
                return true;
            }

            $available = $locked->quantity - $locked->reserved_quantity;
            if ($available < $quantity) {
                return false;
            }

            $locked->increment('reserved_quantity', $quantity);

            InventoryTransaction::create([
                'product_id' => $product->id,
                'type' => 'reservation',
                'quantity' => 0,
                'quantity_before' => $locked->quantity,
                'quantity_after' => $locked->quantity,
                'notes' => "Reserved {$quantity} units",
            ]);

            return true;
        });
    }

    public function releaseReservation(Product $product, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($product, $quantity) {
            $locked = Product::lockForUpdate()->find($product->id);
            $newReserved = max(0, $locked->reserved_quantity - $quantity);
            $locked->update(['reserved_quantity' => $newReserved]);

            InventoryTransaction::create([
                'product_id' => $product->id,
                'type' => 'reservation_release',
                'quantity' => 0,
                'quantity_before' => $locked->quantity,
                'quantity_after' => $locked->quantity,
                'notes' => "Released {$quantity} reserved units",
            ]);
        });
    }

    public function getAvailableStock(Product $product): int
    {
        if (!$product->manage_stock) {
            return PHP_INT_MAX;
        }
        return max(0, $product->quantity - $product->reserved_quantity);
    }

    public function getHistory(Product $product, int $limit = 50)
    {
        return InventoryTransaction::where('product_id', $product->id)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
