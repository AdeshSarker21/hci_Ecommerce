<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderStatusHistoryFactory extends Factory
{
    protected $model = OrderStatusHistory::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'from_status' => null,
            'to_status' => 'pending',
            'type' => 'status',
            'note' => fake()->optional(0.5)->sentence(),
            'metadata' => null,
        ];
    }

    public function statusChange(): static
    {
        return $this->state(fn () => ['type' => 'status']);
    }

    public function paymentChange(): static
    {
        return $this->state(fn () => ['type' => 'payment']);
    }

    public function shipmentChange(): static
    {
        return $this->state(fn () => ['type' => 'shipment']);
    }

    public function note(): static
    {
        return $this->state(fn () => ['type' => 'note']);
    }
}
