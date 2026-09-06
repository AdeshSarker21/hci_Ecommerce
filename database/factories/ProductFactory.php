<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $price = fake()->randomFloat(2, 5, 500);

        return [
            'seller_id' => Seller::factory(),
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'price' => $price,
            'compare_at_price' => $price + fake()->randomFloat(2, 5, 100),
            'cost_price' => $price * 0.6,
            'sku' => 'SKU-' . strtoupper(fake()->bothify('#####')),
            'quantity' => fake()->numberBetween(0, 100),
            'manage_stock' => true,
            'low_stock_threshold' => 5,
            'status' => 'draft',
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    public function pendingReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_review',
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }
}
