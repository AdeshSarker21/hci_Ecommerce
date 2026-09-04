<?php

namespace Database\Factories;

use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Seller>
 */
class SellerFactory extends Factory
{
    protected $model = Seller::class;

    public function definition(): array
    {
        $storeName = fake()->company();

        return [
            'store_name' => $storeName,
            'store_slug' => Str::slug($storeName),
            'store_description' => fake()->paragraph(),
            'contact_email' => fake()->safeEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'contact_website' => fake()->url(),
            'business_address' => fake()->streetAddress(),
            'business_city' => fake()->city(),
            'business_state' => fake()->state(),
            'business_country' => 'US',
            'business_postal_code' => fake()->postcode(),
            'status' => 'pending',
            'commission_rate' => 10.00,
            'is_featured' => false,
            'is_active' => true,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function approved(): static
    {
        return $this->state([
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => 'rejected',
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    public function suspended(): static
    {
        return $this->state([
            'status' => 'suspended',
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }
}
