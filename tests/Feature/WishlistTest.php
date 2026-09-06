<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'status' => 'active',
            'is_active' => true,
        ]);

        $sellerUser = User::factory()->create([
            'status' => 'active',
            'is_active' => true,
        ]);

        $seller = Seller::factory()->create([
            'user_id' => $sellerUser->id,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->product = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => 'published',
            'is_active' => true,
            'price' => 29.99,
            'quantity' => 10,
            'manage_stock' => true,
        ]);
    }

    public function test_guest_cannot_access_wishlist_page(): void
    {
        $response = $this->get(route('wishlist.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_wishlist_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('wishlist.index'));

        $response->assertStatus(200);
        $response->assertViewIs('wishlist.index');
    }

    public function test_user_can_add_product_to_wishlist(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('wishlist.toggle.api', $this->product->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'action' => 'added',
            'wishlist_count' => 1,
        ]);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_user_can_remove_product_from_wishlist(): void
    {
        Wishlist::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('wishlist.toggle.api', $this->product->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'action' => 'removed',
            'wishlist_count' => 0,
        ]);

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_user_can_remove_wishlist_item_directly(): void
    {
        Wishlist::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('wishlist.remove', $this->product->id));

        $response->assertOk();
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_user_can_clear_wishlist(): void
    {
        Wishlist::create(['user_id' => $this->user->id, 'product_id' => $this->product->id]);

        $product2 = Product::factory()->create([
            'seller_id' => $this->product->seller_id,
            'status' => 'published',
            'is_active' => true,
            'quantity' => 5,
            'manage_stock' => true,
        ]);
        Wishlist::create(['user_id' => $this->user->id, 'product_id' => $product2->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('wishlist.clear'));

        $response->assertOk();
        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_user_can_get_wishlist_count(): void
    {
        Wishlist::create(['user_id' => $this->user->id, 'product_id' => $this->product->id]);

        $response = $this->actingAs($this->user)
            ->getJson(route('wishlist.count'));

        $response->assertOk();
        $response->assertJson(['success' => true, 'count' => 1]);
    }

    public function test_user_can_check_if_product_is_wishlisted(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('wishlist.check', $this->product->id));

        $response->assertOk();
        $response->assertJson(['success' => true, 'is_wishlisted' => false]);

        Wishlist::create(['user_id' => $this->user->id, 'product_id' => $this->product->id]);

        $response = $this->actingAs($this->user)
            ->getJson(route('wishlist.check', $this->product->id));

        $response->assertOk();
        $response->assertJson(['success' => true, 'is_wishlisted' => true]);
    }

    public function test_guest_cannot_toggle_wishlist(): void
    {
        $response = $this->postJson(route('wishlist.toggle.api', $this->product->id));

        $response->assertStatus(401);
    }

    public function test_unavailable_product_cannot_be_wishlisted(): void
    {
        $inactiveProduct = Product::factory()->create([
            'seller_id' => $this->product->seller_id,
            'is_active' => false,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('wishlist.toggle.api', $inactiveProduct->id));

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_wishlist_page_shows_items(): void
    {
        Wishlist::create(['user_id' => $this->user->id, 'product_id' => $this->product->id]);

        $response = $this->actingAs($this->user)->get(route('wishlist.index'));

        $response->assertOk();
        $response->assertSee('My Wishlist');
    }

    public function test_service_prevents_duplicate_wishlist_entries(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('wishlist.toggle.api', $this->product->id));

        $response->assertOk();
        $this->assertDatabaseCount('wishlists', 1);

        $response = $this->actingAs($this->user)
            ->postJson(route('wishlist.toggle.api', $this->product->id));

        $response->assertOk();
        $this->assertDatabaseCount('wishlists', 0);
    }
}
