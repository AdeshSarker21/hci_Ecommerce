<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    private Seller $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->seller = Seller::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'store_slug' => 'test-store',
            'store_name' => 'Test Store',
            'store_description' => 'A great test store',
        ]);
    }

    public function test_public_can_view_storefront(): void
    {
        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Published Product',
            'price' => 29.99,
            'status' => 'published',
            'is_active' => true,
        ]);

        $response = $this->get(route('storefront.show', 'test-store'));

        $response->assertOk();
        $response->assertSee('Test Store');
        $response->assertSee('Published Product');
    }

    public function test_storefront_shows_only_published_products(): void
    {
        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Published Product',
            'price' => 29.99,
            'status' => 'published',
            'is_active' => true,
        ]);
        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Draft Product',
            'price' => 19.99,
            'status' => 'draft',
            'is_active' => true,
        ]);

        $response = $this->get(route('storefront.show', 'test-store'));

        $response->assertOk();
        $response->assertSee('Published Product');
        $response->assertDontSee('Draft Product');
    }

    public function test_storefront_search_filters_products(): void
    {
        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Wireless Headphones',
            'price' => 59.99,
            'status' => 'published',
            'is_active' => true,
        ]);
        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Bluetooth Speaker',
            'price' => 39.99,
            'status' => 'published',
            'is_active' => true,
        ]);

        $response = $this->get(route('storefront.show', ['slug' => 'test-store', 'search' => 'Wireless']));

        $response->assertOk();
        $response->assertSee('Wireless Headphones');
        $response->assertDontSee('Bluetooth Speaker');
    }

    public function test_storefront_category_filter(): void
    {
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 'active',
        ]);

        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Phone',
            'price' => 99.99,
            'status' => 'published',
            'is_active' => true,
            'category_id' => $category->id,
        ]);
        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Shirt',
            'price' => 19.99,
            'status' => 'published',
            'is_active' => true,
        ]);

        $response = $this->get(route('storefront.show', ['slug' => 'test-store', 'category_id' => $category->id]));

        $response->assertOk();
        $response->assertSee('Phone');
        $response->assertDontSee('Shirt');
    }

    public function test_storefront_sorting(): void
    {
        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Expensive Product',
            'price' => 99.99,
            'status' => 'published',
            'is_active' => true,
        ]);
        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Cheap Product',
            'price' => 9.99,
            'status' => 'published',
            'is_active' => true,
        ]);

        $response = $this->get(route('storefront.show', ['slug' => 'test-store', 'sort' => 'price_asc']));

        $response->assertOk();
    }

    public function test_storefront_returns_404_for_nonexistent_store(): void
    {
        $response = $this->get(route('storefront.show', 'nonexistent-store'));

        $response->assertNotFound();
    }

    public function test_storefront_returns_404_for_pending_store(): void
    {
        $user = User::factory()->create();
        $pendingSeller = Seller::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'store_slug' => 'pending-store',
        ]);

        $response = $this->get(route('storefront.show', 'pending-store'));

        $response->assertNotFound();
    }

    public function test_storefront_pagination(): void
    {
        for ($i = 0; $i < 15; $i++) {
            Product::create([
                'seller_id' => $this->seller->id,
                'name' => "Product {$i}",
                'price' => 10 + $i,
                'status' => 'published',
                'is_active' => true,
            ]);
        }

        $response = $this->get(route('storefront.show', 'test-store'));

        $response->assertOk();
    }

    public function test_storefront_displays_store_description(): void
    {
        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Product',
            'price' => 10,
            'status' => 'published',
            'is_active' => true,
        ]);

        $response = $this->get(route('storefront.show', 'test-store'));

        $response->assertSee('A great test store');
    }

    public function test_storefront_displays_social_links(): void
    {
        $this->seller->update([
            'facebook_url' => 'https://facebook.com/teststore',
            'instagram_url' => 'https://instagram.com/teststore',
        ]);

        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Product',
            'price' => 10,
            'status' => 'published',
            'is_active' => true,
        ]);

        $response = $this->get(route('storefront.show', 'test-store'));

        $response->assertSee('facebook.com/teststore');
        $response->assertSee('instagram.com/teststore');
    }
}
