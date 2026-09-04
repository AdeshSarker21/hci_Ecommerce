<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected Seller $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = Seller::factory()->create(['user_id' => User::factory()->create()->id]);
    }

    private function createProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'seller_id' => $this->seller->id,
            'name' => 'Test Product',
            'price' => 29.99,
            'stock_quantity' => 10,
            'status' => 'published',
            'is_active' => true,
            'manage_stock' => true,
        ], $overrides));
    }

    public function test_search_page_loads(): void
    {
        $response = $this->get(route('search'));
        $response->assertStatus(200)->assertViewIs('search');
    }

    public function test_search_keyword_filters_products(): void
    {
        $this->createProduct(['name' => 'Laptop Pro', 'slug' => 'laptop-pro', 'sku' => 'LP-001']);
        $this->createProduct(['name' => 'Phone Basic', 'slug' => 'phone-basic', 'sku' => 'PB-001']);

        $response = $this->get(route('search', ['keyword' => 'Laptop']));
        $response->assertStatus(200);
        $response->assertSee('Laptop Pro');
        $response->assertDontSee('Phone Basic');
    }

    public function test_search_category_filter(): void
    {
        $cat = \App\Models\Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'status' => 'active']);
        $this->createProduct(['category_id' => $cat->id, 'name' => 'Widget', 'slug' => 'widget', 'sku' => 'W-001']);
        $this->createProduct(['name' => 'Gadget', 'slug' => 'gadget', 'sku' => 'G-001']);

        $response = $this->get(route('search', ['category_id' => $cat->id]));
        $response->assertStatus(200);
    }

    public function test_search_price_filter(): void
    {
        $this->createProduct(['name' => 'Cheap', 'slug' => 'cheap', 'sku' => 'C-001', 'price' => 50]);
        $this->createProduct(['name' => 'Expensive', 'slug' => 'expensive', 'sku' => 'E-001', 'price' => 500]);

        $response = $this->get(route('search', ['max_price' => 100]));
        $response->assertStatus(200);
    }

    public function test_search_in_stock_filter(): void
    {
        $this->createProduct(['name' => 'In Stock', 'slug' => 'in-stock', 'sku' => 'IS-001', 'stock_quantity' => 10]);
        $this->createProduct(['name' => 'Out of Stock', 'slug' => 'out-of-stock', 'sku' => 'OS-001', 'stock_quantity' => 0]);

        $response = $this->get(route('search', ['in_stock' => '1']));
        $response->assertStatus(200);
    }

    public function test_search_featured_filter(): void
    {
        $this->createProduct(['name' => 'Featured', 'slug' => 'featured', 'sku' => 'F-001', 'is_featured' => true]);
        $this->createProduct(['name' => 'Not Featured', 'slug' => 'not-featured', 'sku' => 'NF-001', 'is_featured' => false]);

        $response = $this->get(route('search', ['is_featured' => '1']));
        $response->assertStatus(200);
    }

    public function test_search_sorting(): void
    {
        $this->createProduct(['name' => 'Expensive', 'slug' => 'exp', 'sku' => 'EX-001', 'price' => 100]);
        $this->createProduct(['name' => 'Cheap', 'slug' => 'chp', 'sku' => 'CH-001', 'price' => 50]);

        $response = $this->get(route('search', ['sort' => 'price_asc']));
        $response->assertStatus(200);
    }

    public function test_product_detail_page_loads(): void
    {
        $product = $this->createProduct(['name' => 'Detail Product', 'slug' => 'detail-product', 'sku' => 'DP-001']);

        $response = $this->get(route('product.show', $product->slug));
        $response->assertStatus(200)->assertViewIs('product.show');
    }

    public function test_product_detail_shows_name(): void
    {
        $product = $this->createProduct(['name' => 'Visible Product', 'slug' => 'visible-product', 'sku' => 'VP-001']);

        $response = $this->get(route('product.show', $product->slug));
        $response->assertSee('Visible Product');
    }

    public function test_unpublished_product_returns_404(): void
    {
        $product = $this->createProduct(['name' => 'Draft', 'slug' => 'draft-product', 'sku' => 'DR-001', 'status' => 'draft']);

        $response = $this->get(route('product.show', $product->slug));
        $response->assertStatus(404);
    }

    public function test_inactive_product_returns_404(): void
    {
        $product = $this->createProduct(['name' => 'Inactive', 'slug' => 'inactive-product', 'sku' => 'IA-001', 'is_active' => false]);

        $response = $this->get(route('product.show', $product->slug));
        $response->assertStatus(404);
    }
}
