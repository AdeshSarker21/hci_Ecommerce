<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'seller_id' => null,
            'name' => fake()->company() . ' Warehouse',
            'code' => 'WH-' . strtoupper(fake()->bothify('??##')),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => 'US',
            'postal_code' => fake()->postcode(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'status' => 'active',
            'is_default' => false,
        ];
    }

    public function forSeller(Seller $seller): static
    {
        return $this->state(['seller_id' => $seller->id]);
    }

    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }

    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }
}
