<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['home', 'office', 'other']),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->optional(0.5)->safeEmail(),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->optional(0.3)->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => 'Bangladesh',
            'is_default' => false,
        ];
    }

    public function home(): static
    {
        return $this->state(fn () => ['label' => 'home']);
    }

    public function office(): static
    {
        return $this->state(fn () => ['label' => 'office']);
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }
}
