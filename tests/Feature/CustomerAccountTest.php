<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(): User
    {
        $role = Role::firstOrCreate(
            ['slug' => 'customer'],
            ['name' => 'Customer', 'is_system' => true]
        );

        $user = User::factory()->create([
            'status' => 'active',
            'is_active' => true,
            'email_verified_at' => now(),
            'phone' => '+8801712345678',
        ]);

        $user->roles()->attach($role);

        return $user;
    }

    private function createInactiveCustomer(): User
    {
        return User::factory()->create([
            'status' => 'inactive',
            'is_active' => false,
        ]);
    }

    // ── Dashboard Tests ──

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_inactive_user_gets_redirected(): void
    {
        $user = $this->createInactiveCustomer();
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(302);
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = $this->createCustomer();
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk()
            ->assertViewIs('account.dashboard')
            ->assertViewHas(['user', 'stats', 'recentOrders']);
    }

    public function test_dashboard_shows_correct_stats(): void
    {
        $user = $this->createCustomer();

        $seller = \App\Models\Seller::factory()->create(['user_id' => $user->id]);

        \App\Models\Order::factory()->create(['user_id' => $user->id, 'seller_id' => $seller->id, 'status' => 'pending']);
        \App\Models\Order::factory()->create(['user_id' => $user->id, 'seller_id' => $seller->id, 'status' => 'pending']);
        \App\Models\Order::factory()->create(['user_id' => $user->id, 'seller_id' => $seller->id, 'status' => 'delivered']);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();

        $stats = $response->viewData('stats');
        $this->assertEquals(3, $stats['total_orders']);
        $this->assertEquals(2, $stats['pending_orders']);
        $this->assertEquals(1, $stats['delivered_orders']);
    }

    // ── Profile Tests ──

    public function test_user_can_view_profile_page(): void
    {
        $user = $this->createCustomer();
        $response = $this->actingAs($user)->get(route('account.profile'));
        $response->assertOk()
            ->assertViewIs('account.profile')
            ->assertViewHas('user');
    }

    public function test_user_can_update_profile(): void
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'phone' => '+8801999999999',
        ]);

        $response->assertRedirect(route('account.profile'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'phone' => '+8801999999999',
        ]);
    }

    public function test_profile_update_validates_required_fields(): void
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => '',
            'email' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_profile_update_validates_unique_email(): void
    {
        $user = $this->createCustomer();
        $other = User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => 'Test',
            'email' => 'taken@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('public');
        $user = $this->createCustomer();

        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $file,
        ]);

        $response->assertRedirect(route('account.profile'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
        Storage::disk('public')->assertExists('avatars/' . $file->hashName());
    }

    public function test_user_cannot_update_other_users_profile(): void
    {
        $user = $this->createCustomer();
        $other = $this->createCustomer();

        $response = $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => 'Hacked',
            'email' => $user->email,
        ]);

        // Should update own profile, not others
        $response->assertRedirect(route('account.profile'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Hacked',
        ]);
    }

    // ── Password Tests ──

    public function test_user_can_view_password_page(): void
    {
        $user = $this->createCustomer();
        $response = $this->actingAs($user)->get(route('account.password'));
        $response->assertOk()
            ->assertViewIs('account.password');
    }

    public function test_user_can_change_password(): void
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'password',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        $response->assertRedirect(route('account.password'))
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewSecurePassword123!', $user->password));
    }

    public function test_password_change_requires_current_password(): void
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    public function test_password_change_requires_confirmation(): void
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'password',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'DifferentPassword!',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_password_change_validates_minimum_length(): void
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    // ── Address Tests ──

    public function test_user_can_view_addresses_page(): void
    {
        $user = $this->createCustomer();
        $response = $this->actingAs($user)->get(route('account.addresses'));
        $response->assertOk()
            ->assertViewIs('account.addresses')
            ->assertViewHas('addresses');
    }

    public function test_user_can_add_address(): void
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->postJson(route('account.addresses.store'), [
            'label' => 'home',
            'name' => 'John Doe',
            'phone' => '+8801712345678',
            'email' => 'john@example.com',
            'address_line_1' => '123 Main Street',
            'address_line_2' => 'Apt 4B',
            'city' => 'Dhaka',
            'state' => 'Dhaka Division',
            'postal_code' => '1205',
            'country' => 'Bangladesh',
            'is_default' => true,
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'label' => 'home',
            'city' => 'Dhaka',
            'is_default' => true,
        ]);
    }

    public function test_first_address_becomes_default(): void
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->postJson(route('account.addresses.store'), [
            'label' => 'home',
            'name' => 'John Doe',
            'phone' => '+8801712345678',
            'address_line_1' => '123 Main Street',
            'city' => 'Dhaka',
            'country' => 'Bangladesh',
        ]);

        $response->assertOk();

        $address = Address::where('user_id', $user->id)->first();
        $this->assertTrue($address->is_default);
    }

    public function test_user_can_update_address(): void
    {
        $user = $this->createCustomer();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->putJson(route('account.addresses.update', $address->id), [
            'label' => 'office',
            'name' => 'Updated Name',
            'phone' => '+8801999999999',
            'address_line_1' => '456 New Street',
            'city' => 'Chittagong',
            'country' => 'Bangladesh',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'city' => 'Chittagong',
        ]);
    }

    public function test_user_can_delete_address(): void
    {
        $user = $this->createCustomer();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson(route('account.addresses.delete', $address->id));

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_user_can_set_default_address(): void
    {
        $user = $this->createCustomer();
        $address1 = Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);
        $address2 = Address::factory()->create(['user_id' => $user->id, 'is_default' => false]);

        $response = $this->actingAs($user)->postJson(route('account.addresses.default', $address2->id));

        $response->assertOk()
            ->assertJson(['success' => true]);

        $address1->refresh();
        $address2->refresh();
        $this->assertFalse($address1->is_default);
        $this->assertTrue($address2->is_default);
    }

    public function test_user_cannot_access_other_users_address(): void
    {
        $user = $this->createCustomer();
        $other = $this->createCustomer();
        $otherAddress = Address::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->putJson(route('account.addresses.update', $otherAddress->id), [
            'label' => 'hacked',
            'name' => 'Hacker',
            'phone' => '+8800000000000',
            'address_line_1' => '123 Hack Street',
            'city' => 'Dhaka',
            'country' => 'Bangladesh',
        ]);

        $response->assertStatus(404);
    }

    public function test_address_validation_requires_fields(): void
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->postJson(route('account.addresses.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'label', 'name', 'phone', 'address_line_1', 'city', 'country',
            ]);
    }

    public function test_user_cannot_delete_other_users_address(): void
    {
        $user = $this->createCustomer();
        $other = $this->createCustomer();
        $otherAddress = Address::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->deleteJson(route('account.addresses.delete', $otherAddress->id));

        $response->assertStatus(404);
        $this->assertDatabaseHas('addresses', ['id' => $otherAddress->id]);
    }

    // ── Orders Tests ──

    public function test_user_can_view_orders_page(): void
    {
        $user = $this->createCustomer();
        $response = $this->actingAs($user)->get(route('account.orders'));
        $response->assertOk()
            ->assertViewIs('account.orders-list')
            ->assertViewHas(['orders', 'stats']);
    }

    public function test_user_can_view_wishlist_page(): void
    {
        $user = $this->createCustomer();
        $response = $this->actingAs($user)->get(route('account.wishlist'));
        $response->assertOk()
            ->assertViewIs('account.wishlist-list');
    }

    public function test_user_can_view_recently_viewed_page(): void
    {
        $user = $this->createCustomer();
        $response = $this->actingAs($user)->get(route('account.recently-viewed'));
        $response->assertOk()
            ->assertViewIs('account.recently-viewed');
    }

    public function test_user_can_view_reviews_page(): void
    {
        $user = $this->createCustomer();
        $response = $this->actingAs($user)->get(route('account.reviews'));
        $response->assertOk()
            ->assertViewIs('account.reviews');
    }

    public function test_user_can_view_notifications_page(): void
    {
        $user = $this->createCustomer();
        $response = $this->actingAs($user)->get(route('account.notifications'));
        $response->assertOk()
            ->assertViewIs('account.notifications');
    }

    public function test_user_can_view_wallet_page(): void
    {
        $user = $this->createCustomer();
        $response = $this->actingAs($user)->get(route('account.wallet'));
        $response->assertOk()
            ->assertViewIs('account.wallet');
    }

    public function test_user_can_view_settings_page(): void
    {
        $user = $this->createCustomer();
        $response = $this->actingAs($user)->get(route('account.settings'));
        $response->assertOk()
            ->assertViewIs('account.settings');
    }

    public function test_user_can_update_settings(): void
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->put(route('account.settings.update'), [
            'timezone' => 'Asia/Dhaka',
            'locale' => 'bn',
        ]);

        $response->assertRedirect(route('account.settings'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'timezone' => 'Asia/Dhaka',
            'locale' => 'bn',
        ]);
    }

    public function test_settings_validates_locale(): void
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->put(route('account.settings.update'), [
            'timezone' => 'UTC',
            'locale' => 'invalid',
        ]);

        $response->assertSessionHasErrors(['locale']);
    }

    // ── Authorization Tests ──

    public function test_guest_cannot_access_any_account_page(): void
    {
        $routes = [
            route('dashboard'),
            route('account.profile'),
            route('account.password'),
            route('account.addresses'),
            route('account.orders'),
            route('account.wishlist'),
            route('account.recently-viewed'),
            route('account.reviews'),
            route('account.notifications'),
            route('account.wallet'),
            route('account.settings'),
        ];

        foreach ($routes as $url) {
            $response = $this->get($url);
            $response->assertRedirect(route('login'));
        }
    }
}
