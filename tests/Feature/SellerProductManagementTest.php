<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $sellerUser;
    private Seller $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sellerUser = User::factory()->create();
        $this->seller = Seller::factory()->create([
            'user_id' => $this->sellerUser->id,
            'status' => 'approved',
            'store_slug' => 'test-seller-store',
        ]);
    }

    public function test_seller_can_view_products_with_stats(): void
    {
        Product::create(['seller_id' => $this->seller->id, 'name' => 'Draft', 'price' => 10, 'status' => 'draft']);
        Product::create(['seller_id' => $this->seller->id, 'name' => 'Published', 'price' => 10, 'status' => 'published']);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.products.index'));

        $response->assertOk();
    }

    public function test_seller_can_filter_products_by_category(): void
    {
        $category = \App\Models\Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 'active',
        ]);

        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Phone',
            'price' => 99,
            'status' => 'published',
            'category_id' => $category->id,
        ]);
        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Shirt',
            'price' => 19,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.products.index', ['category_id' => $category->id]));

        $response->assertOk();
    }

    public function test_seller_can_sort_products(): void
    {
        Product::create(['seller_id' => $this->seller->id, 'name' => 'Expensive', 'price' => 100, 'status' => 'published']);
        Product::create(['seller_id' => $this->seller->id, 'name' => 'Cheap', 'price' => 5, 'status' => 'published']);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.products.index', ['sort' => 'price_asc']));

        $response->assertOk();
    }

    public function test_seller_can_search_products(): void
    {
        Product::create(['seller_id' => $this->seller->id, 'name' => 'Wireless Headphones', 'price' => 59, 'status' => 'published']);
        Product::create(['seller_id' => $this->seller->id, 'name' => 'Bluetooth Speaker', 'price' => 39, 'status' => 'published']);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.products.index', ['search' => 'Wireless']));

        $response->assertOk();
    }

    public function test_seller_can_duplicate_draft_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Original Product',
            'name_bn' => 'মূল পণ্য',
            'price' => 29.99,
            'status' => 'draft',
            'category_id' => null,
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.duplicate', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'seller_id' => $this->seller->id,
            'name' => 'Original Product (Copy)',
            'status' => 'draft',
        ]);
    }

    public function test_seller_can_duplicate_rejected_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Rejected Product',
            'price' => 29.99,
            'status' => 'rejected',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.duplicate', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'seller_id' => $this->seller->id,
            'name' => 'Rejected Product (Copy)',
            'status' => 'draft',
        ]);
    }

    public function test_seller_cannot_duplicate_published_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Published Product',
            'price' => 29.99,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.duplicate', $product));

        $response->assertRedirect();
    }

    public function test_seller_cannot_duplicate_other_seller_product(): void
    {
        $otherSeller = Seller::factory()->create(['user_id' => User::factory()->create()->id, 'status' => 'approved']);
        $product = Product::create([
            'seller_id' => $otherSeller->id,
            'name' => 'Other Product',
            'price' => 19.99,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.duplicate', $product));

        $response->assertForbidden();
    }

    public function test_seller_can_publish_approved_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Approved Product',
            'price' => 29.99,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.publish', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'published',
        ]);
    }

    public function test_seller_cannot_publish_draft_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Draft Product',
            'price' => 29.99,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.publish', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'draft',
        ]);
    }

    public function test_seller_can_unpublish_published_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Published Product',
            'price' => 29.99,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.unpublish', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'approved',
        ]);
    }

    public function test_seller_cannot_unpublish_draft_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Draft Product',
            'price' => 29.99,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.unpublish', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'draft',
        ]);
    }

    public function test_seller_cannot_publish_other_seller_product(): void
    {
        $otherSeller = Seller::factory()->create(['user_id' => User::factory()->create()->id, 'status' => 'approved']);
        $product = Product::create([
            'seller_id' => $otherSeller->id,
            'name' => 'Other Product',
            'price' => 19.99,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.publish', $product));

        $response->assertForbidden();
    }

    public function test_seller_can_update_store_settings(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->put(route('seller.profile.update'), [
                'store_name' => 'Updated Store Name',
                'store_tagline' => 'Best store ever',
                'store_description' => 'Updated description',
                'store_description_bn' => 'আপডেটেড বিবরণ',
                'shipping_policy' => 'We ship within 3 days',
                'return_policy' => '30 day returns',
                'about_us' => 'We are the best',
                'facebook_url' => 'https://facebook.com/test',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sellers', [
            'id' => $this->seller->id,
            'store_name' => 'Updated Store Name',
            'store_tagline' => 'Best store ever',
        ]);
    }

    public function test_seller_storefront_is_accessible(): void
    {
        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Store Product',
            'price' => 29.99,
            'status' => 'published',
            'is_active' => true,
        ]);

        $response = $this->get(route('storefront.show', 'test-seller-store'));

        $response->assertOk();
        $response->assertSee('Store Product');
    }

    public function test_pending_seller_redirected_from_products(): void
    {
        $pendingUser = User::factory()->create();
        Seller::factory()->create([
            'user_id' => $pendingUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($pendingUser)
            ->get(route('seller.products.index'));

        $response->assertRedirect(route('seller.dashboard'));
    }
}
