<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitPrice = fake()->randomFloat(2, 5, 200);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'product_sku' => 'SKU-' . strtoupper(fake()->bothify('####')),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => round($quantity * $unitPrice, 2),
        ];
    }
}
