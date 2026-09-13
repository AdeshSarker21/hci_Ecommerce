<?php

namespace Tests\Feature;

use App\Models\Cart\CartItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRedirectFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $roleSlug = 'customer'): User
    {
        $role = Role::firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => ucfirst($roleSlug), 'is_system' => true]
        );

        $user = User::factory()->create([
            'status' => 'active',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $user->roles()->attach($role);

        return $user;
    }

    // ── Guest → Checkout → Login → Checkout ──

    public function test_guest_checkout_redirects_to_login(): void
    {
        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_checkout_intended_url_is_stored_in_session(): void
    {
        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('login'));

        $response->assertSessionHas('url.intended', url('/checkout'));
    }

    public function test_login_redirects_to_checkout_when_intended(): void
    {
        $user = $this->createUserWithRole('customer');

        // Simulate guest visiting checkout (stores intended URL)
        $this->get(route('checkout.index'));

        // Login
        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(url('/checkout'));
    }

    public function test_login_after_checkout_preserves_cart(): void
    {
        $user = $this->createUserWithRole('customer');
        $sellerUser = $this->createUserWithRole('seller');

        // Create a product with an explicit seller
        $product = Product::factory()->create([
            'quantity' => 10,
            'price' => 29.99,
            'seller_id' => \App\Models\Seller::factory()->create(['user_id' => $sellerUser->id])->id,
        ]);

        // Simulate guest visiting checkout
        $this->get(route('checkout.index'));

        // Login and assert redirect to checkout
        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(url('/checkout'));

        // Assert user is logged in
        $this->assertAuthenticatedAs($user);
    }

    // ── Guest → Checkout → Register → Checkout ──

    public function test_register_redirects_to_checkout_when_intended(): void
    {
        // Simulate guest visiting checkout
        $this->get(route('checkout.index'));

        // Register
        $response = $this->post(route('register'), [
            'name' => 'New Customer',
            'email' => 'newcustomer@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(url('/checkout'));
        $this->assertDatabaseHas('users', ['email' => 'newcustomer@example.com']);
    }

    // ── Customer Login → Customer Dashboard ──

    public function test_customer_login_redirects_to_dashboard(): void
    {
        $user = $this->createUserWithRole('customer');

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_customer_register_redirects_to_dashboard(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    // ── Admin Login → Admin Dashboard ──

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $user = $this->createUserWithRole('admin');

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_super_admin_login_redirects_to_admin_dashboard(): void
    {
        $user = $this->createUserWithRole('super-admin');

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_manager_login_redirects_to_admin_dashboard(): void
    {
        $user = $this->createUserWithRole('manager');

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    // ── Seller Login → Seller Dashboard ──

    public function test_seller_login_redirects_to_seller_dashboard(): void
    {
        $user = $this->createUserWithRole('seller');

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('seller.dashboard'));
    }

    public function test_seller_staff_login_redirects_to_seller_dashboard(): void
    {
        $user = $this->createUserWithRole('seller-staff');

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('seller.dashboard'));
    }

    // ── Cart Preservation ──

    public function test_guest_cart_is_preserved_after_login(): void
    {
        $user = $this->createUserWithRole('customer');
        $sellerUser = $this->createUserWithRole('seller');

        // Create a product with an explicit seller
        $product = Product::factory()->create([
            'quantity' => 10,
            'price' => 49.99,
            'seller_id' => \App\Models\Seller::factory()->create(['user_id' => $sellerUser->id])->id,
        ]);

        // Simulate guest session with cart items
        $this->session([
            'cart_session_id' => 'test-session-123',
        ]);

        // Add item to cart as guest
        \App\Models\CartItem::create([
            'session_id' => 'test-session-123',
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => $product->price,
        ]);

        // Verify guest cart exists
        $this->assertDatabaseHas('cart_items', [
            'session_id' => 'test-session-123',
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Login
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Cart should be merged to user
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_guest_cart_is_preserved_after_register(): void
    {
        $sellerUser = $this->createUserWithRole('seller');

        // Create a product with an explicit seller
        $product = Product::factory()->create([
            'quantity' => 10,
            'price' => 39.99,
            'seller_id' => \App\Models\Seller::factory()->create(['user_id' => $sellerUser->id])->id,
        ]);

        // Simulate guest session with cart items
        $this->session([
            'cart_session_id' => 'test-session-456',
        ]);

        // Add item to cart as guest
        \App\Models\CartItem::create([
            'session_id' => 'test-session-456',
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => $product->price,
        ]);

        // Register
        $this->post(route('register'), [
            'name' => 'New Buyer',
            'email' => 'newbuyer@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $user = User::where('email', 'newbuyer@example.com')->first();

        // Cart should be merged to new user
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    // ── Role-Based Redirect After Login ──

    public function test_authenticated_user_visiting_login_gets_role_based_redirect(): void
    {
        $customer = $this->createUserWithRole('customer');
        $admin = $this->createUserWithRole('admin');
        $seller = $this->createUserWithRole('seller');

        // Customer visits /login
        $response = $this->actingAs($customer)->get(route('login'));
        $response->assertRedirect(route('dashboard'));

        // Admin visits /login
        $response = $this->actingAs($admin)->get(route('login'));
        $response->assertRedirect(route('admin.dashboard'));

        // Seller visits /login
        $response = $this->actingAs($seller)->get(route('login'));
        $response->assertRedirect(route('seller.dashboard'));
    }

    // ── Unauthorized Access Prevention ──

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $user = $this->createUserWithRole('customer');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_seller_dashboard(): void
    {
        $user = $this->createUserWithRole('customer');

        $response = $this->actingAs($user)->get(route('seller.dashboard'));
        $response->assertStatus(403);
    }

    public function test_seller_cannot_access_admin_dashboard(): void
    {
        $user = $this->createUserWithRole('seller');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_seller_dashboard(): void
    {
        $user = $this->createUserWithRole('admin');

        $response = $this->actingAs($user)->get(route('seller.dashboard'));
        $response->assertStatus(403);
    }

    // ── Login View Context ──

    public function test_login_view_shows_checkout_message_when_coming_from_checkout(): void
    {
        $response = $this->get(route('login', ['intended' => '/checkout']));
        $response->assertOk()
            ->assertSee('Login required for checkout')
            ->assertSee('Your cart items will be preserved');
    }

    public function test_register_view_shows_checkout_message_when_coming_from_checkout(): void
    {
        $response = $this->get(route('register', ['intended' => '/checkout']));
        $response->assertOk()
            ->assertSee('Account required for checkout')
            ->assertSee('Your cart items will be preserved');
    }

    public function test_login_view_does_not_show_checkout_message_for_normal_login(): void
    {
        $response = $this->get(route('login'));
        $response->assertOk()
            ->assertDontSee('Login required for checkout');
    }

    // ── Logout ──

    public function test_logout_redirects_to_login(): void
    {
        $user = $this->createUserWithRole('customer');

        $response = $this->actingAs($user)->post(route('logout'));
        $response->assertRedirect(route('login'));
    }

    // ── Session Preservation ──

    public function test_session_is_preserved_during_login_flow(): void
    {
        $user = $this->createUserWithRole('customer');

        // Set some session data
        $this->session(['test_key' => 'test_value']);

        // Login
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Session data should be preserved
        $this->assertEquals('test_value', session('test_key'));
    }
}
