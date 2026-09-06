<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Models\RecentlyViewed;
use App\Services\RecentlyViewedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecentlyViewedTest extends TestCase
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
        ]);

        $this->sellerId = $seller->id;
    }

    public function test_guest_can_track_recently_viewed(): void
    {
        $service = new RecentlyViewedService();

        $this->session(['recently_viewed_session_id' => 'test_session_123']);

        $service->track($this->product->id);

        $this->assertDatabaseHas('recently_viewed_products', [
            'session_id' => 'test_session_123',
            'product_id' => $this->product->id,
        ]);
    }

    public function test_authenticated_user_can_track_recently_viewed(): void
    {
        $this->actingAs($this->user);

        $service = new RecentlyViewedService();
        $service->track($this->product->id);

        $this->assertDatabaseHas('recently_viewed_products', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_recently_viewed_does_not_duplicate(): void
    {
        $this->actingAs($this->user);

        $service = new RecentlyViewedService();
        $service->track($this->product->id);
        $service->track($this->product->id);

        $this->assertDatabaseCount('recently_viewed_products', 1);
    }

    public function test_recently_viewed_limits_items(): void
    {
        $this->actingAs($this->user);

        $service = new RecentlyViewedService();

        $products = Product::factory()->count(25)->create([
            'seller_id' => $this->sellerId,
            'status' => 'published',
            'is_active' => true,
        ]);

        foreach ($products as $product) {
            $service->track($product->id);
        }

        $this->assertDatabaseCount('recently_viewed_products', 20);
    }

    public function test_guest_data_merges_on_login(): void
    {
        $this->session(['recently_viewed_session_id' => 'guest_session_123']);

        $service = new RecentlyViewedService();
        $service->track($this->product->id);

        $this->assertDatabaseHas('recently_viewed_products', [
            'session_id' => 'guest_session_123',
            'product_id' => $this->product->id,
        ]);

        $service->mergeGuestData($this->user->id);

        $this->assertDatabaseHas('recently_viewed_products', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $this->assertDatabaseMissing('recently_viewed_products', [
            'session_id' => 'guest_session_123',
            'user_id' => null,
        ]);
    }

    public function test_guest_can_get_recently_viewed_items(): void
    {
        $this->session(['recently_viewed_session_id' => 'guest_session_456']);

        RecentlyViewed::create([
            'session_id' => 'guest_session_456',
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson(route('recently-viewed'));

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_authenticated_user_can_get_recently_viewed_items(): void
    {
        RecentlyViewed::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('recently-viewed'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_recently_viewed_track_endpoint_works(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('recently-viewed.track', $this->product->id));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('recently_viewed_products', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }
}
