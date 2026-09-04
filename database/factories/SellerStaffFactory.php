<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\SellerStaff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SellerStaff>
 */
class SellerStaffFactory extends Factory
{
    protected $model = SellerStaff::class;

    public function definition(): array
    {
        return [
            'seller_id' => Seller::factory(),
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'role' => 'staff',
            'can_manage_products' => false,
            'can_manage_orders' => false,
            'can_manage_settings' => false,
            'can_view_reports' => false,
            'is_active' => true,
            'invited_at' => now(),
        ];
    }
}
