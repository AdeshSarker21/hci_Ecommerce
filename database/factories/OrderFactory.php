<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 10, 500);
        $tax = round($subtotal * 0.1, 2);
        $shippingCost = fake()->randomFloat(2, 0, 20);
        $discount = fake()->randomFloat(2, 0, 50);
        $total = round($subtotal + $tax + $shippingCost - $discount, 2);

        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $paymentStatuses = ['pending', 'paid', 'failed'];

        return [
            'seller_id' => Seller::factory(),
            'user_id' => User::factory(),
            'status' => fake()->randomElement($statuses),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping_cost' => $shippingCost,
            'discount' => $discount,
            'total' => $total,
            'currency' => 'USD',
            'notes' => fake()->optional(0.3)->sentence(),
            'shipping_address' => [
                'name' => fake()->name(),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'zip' => fake()->postcode(),
                'country' => fake()->countryCode(),
            ],
            'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'bank_transfer', 'cod']),
            'payment_status' => fake()->randomElement($paymentStatuses),
            'paid_at' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
            'shipped_at' => fake()->optional(0.4)->dateTimeBetween('-20 days', 'now'),
            'delivered_at' => fake()->optional(0.3)->dateTimeBetween('-10 days', 'now'),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
            'paid_at' => null,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
            'payment_status' => 'paid',
            'paid_at' => now()->subDays(2),
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'shipped',
            'payment_status' => 'paid',
            'paid_at' => now()->subDays(5),
            'shipped_at' => now()->subDays(2),
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'payment_status' => 'paid',
            'paid_at' => now()->subDays(10),
            'shipped_at' => now()->subDays(7),
            'delivered_at' => now()->subDays(3),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now()->subDays(1),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
            'paid_at' => now()->subDays(1),
        ]);
    }
}
