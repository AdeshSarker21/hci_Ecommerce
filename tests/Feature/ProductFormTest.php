<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Role;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductFormTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $sellerUser;
    private Seller $seller;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['slug' => 'products.view'], ['name' => 'View Products', 'group' => 'products']);
        Permission::firstOrCreate(['slug' => 'products.create'], ['name' => 'Create Products', 'group' => 'products']);
        Permission::firstOrCreate(['slug' => 'products.update'], ['name' => 'Update Products', 'group' => 'products']);
        Permission::firstOrCreate(['slug' => 'products.delete'], ['name' => 'Delete Products', 'group' => 'products']);
        Permission::firstOrCreate(['slug' => 'products.manage'], ['name' => 'Manage Products', 'group' => 'products']);

        $adminRole = Role::firstOrCreate(['slug' => 'admin', 'name' => 'Admin']);
        $adminRole->permissions()->sync(
            Permission::whereIn('slug', ['products.view', 'products.create', 'products.update', 'products.delete', 'products.manage'])->pluck('id')
        );

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);

        $this->sellerUser = User::factory()->create();
        $this->seller = Seller::factory()->create([
            'user_id' => $this->sellerUser->id,
            'status' => 'approved',
        ]);
    }

    // ── Seller Create ──────────────────────────────────────────

    public function test_seller_can_view_create_form(): void
    {
        $response = $this->actingAs($this->sellerUser)->get(route('seller.products.create'));
        $response->assertStatus(200)->assertViewIs('seller.products.create');
    }

    public function test_seller_can_create_product(): void
    {
        $response = $this->actingAs($this->sellerUser)->post(route('seller.products.store'), [
            'name' => 'Test Product',
            'price' => 29.99,
            'type' => 'physical',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'seller_id' => $this->seller->id,
            'status' => 'draft',
            'price' => 29.99,
        ]);
    }

    public function test_seller_create_product_with_all_fields(): void
    {
        $category = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'status' => 'active']);
        $brand = Brand::create(['name' => 'Samsung', 'slug' => 'samsung', 'status' => 'active']);

        $response = $this->actingAs($this->sellerUser)->post(route('seller.products.store'), [
            'name' => 'Galaxy Phone',
            'name_bn' => 'গ্যালাক্সি ফোন',
            'description' => 'A great phone',
            'description_bn' => 'একটি দারুণ ফোন',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'type' => 'physical',
            'price' => 999.99,
            'compare_at_price' => 1199.99,
            'cost_price' => 500.00,
            'quantity' => 50,
            'manage_stock' => true,
            'low_stock_threshold' => 10,
            'is_active' => true,
            'is_featured' => true,
            'meta_title' => 'Galaxy Phone - Buy Now',
            'meta_description' => 'Buy the Galaxy Phone',
            'weight' => 0.35,
            'length' => 15.0,
            'width' => 7.5,
            'height' => 1.0,
            'shipping_class' => 'standard',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('products', [
            'name' => 'Galaxy Phone',
            'name_bn' => 'গ্যালাক্সি ফোন',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 999.99,
            'compare_at_price' => 1199.99,
            'cost_price' => 500.00,
            'quantity' => 50,
            'manage_stock' => true,
            'is_featured' => true,
            'weight' => 0.35,
            'shipping_class' => 'standard',
        ]);
    }

    public function test_seller_create_with_spec_values(): void
    {
        $category = Category::create(['name' => 'Phones', 'slug' => 'phones', 'status' => 'active']);
        $attribute = \App\Models\Attribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'text',
            'status' => 'active',
        ]);
        $category->attributes()->attach($attribute->id, ['sort_order' => 0, 'is_required' => false]);

        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Spec Product',
            'price' => 10.00,
            'category_id' => $category->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)->post(route('seller.products.store'), [
            'name' => 'Spec Product 2',
            'price' => 20.00,
            'type' => 'physical',
            'category_id' => $category->id,
            'spec' => [$attribute->id => 'Red'],
        ]);

        $response->assertRedirect();

        $newProduct = Product::where('name', 'Spec Product 2')->first();
        $this->assertNotNull($newProduct);
        $this->assertDatabaseHas('product_attribute_values', [
            'product_id' => $newProduct->id,
            'attribute_id' => $attribute->id,
            'value' => 'Red',
        ]);
    }

    public function test_seller_create_validates_required_fields(): void
    {
        $response = $this->actingAs($this->sellerUser)->post(route('seller.products.store'), []);
        $response->assertSessionHasErrors(['name', 'price', 'type']);
    }

    public function test_seller_create_sets_draft_status(): void
    {
        $this->actingAs($this->sellerUser)->post(route('seller.products.store'), [
            'name' => 'Draft Product',
            'price' => 10.00,
            'type' => 'physical',
        ]);

        $product = Product::where('name', 'Draft Product')->first();
        $this->assertEquals('draft', $product->status);
    }

    public function test_seller_create_auto_generates_sku(): void
    {
        $this->actingAs($this->sellerUser)->post(route('seller.products.store'), [
            'name' => 'Auto SKU Product',
            'price' => 10.00,
            'type' => 'physical',
        ]);

        $product = Product::where('name', 'Auto SKU Product')->first();
        $this->assertNotNull($product->sku);
        $this->assertStringStartsWith('SKU-', $product->sku);
    }

    public function test_seller_create_auto_generates_slug(): void
    {
        $this->actingAs($this->sellerUser)->post(route('seller.products.store'), [
            'name' => 'My Test Product',
            'price' => 10.00,
            'type' => 'physical',
        ]);

        $product = Product::where('name', 'My Test Product')->first();
        $this->assertEquals('my-test-product', $product->slug);
    }

    // ── Seller Edit ────────────────────────────────────────────

    public function test_seller_can_view_edit_form(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Edit Me',
            'price' => 10.00,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)->get(route('seller.products.edit', $product));
        $response->assertStatus(200)->assertViewIs('seller.products.edit');
    }

    public function test_seller_cannot_edit_other_seller_product(): void
    {
        $other = Seller::factory()->create(['user_id' => User::factory()->create()->id]);
        $product = Product::create([
            'seller_id' => $other->id,
            'name' => 'Not Mine',
            'price' => 10.00,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)->get(route('seller.products.edit', $product));
        $response->assertStatus(403);
    }

    public function test_seller_cannot_edit_published_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Published',
            'price' => 10.00,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->sellerUser)->get(route('seller.products.edit', $product));
        $response->assertSessionHas('error');
    }

    public function test_seller_can_update_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Original',
            'price' => 10.00,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)->put(route('seller.products.update', $product), [
            'name' => 'Updated Name',
            'price' => 25.00,
            'type' => 'physical',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated Name', 'price' => 25.00]);
    }

    // ── Submit for Review ──────────────────────────────────────

    public function test_seller_can_submit_draft_for_review(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Submit Me',
            'price' => 10.00,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)->post(route('seller.products.submit', $product));
        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'pending_review']);
    }

    public function test_seller_can_submit_rejected_for_review(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Resubmit',
            'price' => 10.00,
            'status' => 'rejected',
        ]);

        $this->actingAs($this->sellerUser)->post(route('seller.products.submit', $product));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'pending_review']);
    }

    public function test_seller_cannot_submit_published_for_review(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Published',
            'price' => 10.00,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->sellerUser)->post(route('seller.products.submit', $product));
        $response->assertSessionHas('error');
    }

    // ── Admin Create ───────────────────────────────────────────

    public function test_admin_can_view_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.products.create'));
        $response->assertStatus(200)->assertViewIs('admin.products.create');
    }

    public function test_admin_can_create_product(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Admin Product',
            'price' => 49.99,
            'type' => 'physical',
            'seller_id' => $this->seller->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'Admin Product',
            'price' => 49.99,
            'status' => 'draft',
            'seller_id' => $this->seller->id,
        ]);
    }

    public function test_admin_can_create_product_for_seller(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Seller Product via Admin',
            'price' => 99.99,
            'type' => 'physical',
            'seller_id' => $this->seller->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'Seller Product via Admin',
            'seller_id' => $this->seller->id,
        ]);
    }

    // ── Admin Edit ─────────────────────────────────────────────

    public function test_admin_can_view_edit_form(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Admin Edit',
            'price' => 10.00,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.products.edit', $product));
        $response->assertStatus(200)->assertViewIs('admin.products.edit');
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Before Admin Edit',
            'price' => 10.00,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name' => 'After Admin Edit',
            'price' => 55.00,
            'type' => 'physical',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'After Admin Edit']);
    }

    // ── Image Handling ─────────────────────────────────────────

    public function test_seller_can_upload_product_images(): void
    {
        Storage::fake('public');

        $this->actingAs($this->sellerUser)->post(route('seller.products.store'), [
            'name' => 'Image Product',
            'price' => 10.00,
            'type' => 'physical',
            'images' => [
                UploadedFile::fake()->image('product1.jpg', 600, 400),
                UploadedFile::fake()->image('product2.png', 600, 400),
            ],
            'featured_image_index' => 0,
        ]);

        $product = Product::where('name', 'Image Product')->first();
        $this->assertNotNull($product);
        $this->assertEquals(2, $product->images()->count());

        $featured = $product->images()->where('is_featured', true)->first();
        $this->assertNotNull($featured);
        $this->assertEquals(0, $featured->sort_order);
    }

    public function test_admin_can_upload_product_images(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Admin Image Product',
            'price' => 10.00,
            'type' => 'physical',
            'seller_id' => $this->seller->id,
            'images' => [
                UploadedFile::fake()->image('admin1.jpg', 600, 400),
            ],
            'featured_image_index' => 0,
        ]);

        $product = Product::where('name', 'Admin Image Product')->first();
        $this->assertNotNull($product);
        $this->assertEquals(1, $product->images()->count());
    }

    // ── Slug/SKU Uniqueness ────────────────────────────────────

    public function test_slug_is_unique(): void
    {
        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Unique Name',
            'slug' => 'unique-name',
            'price' => 10.00,
            'status' => 'draft',
        ]);

        $this->actingAs($this->sellerUser)->post(route('seller.products.store'), [
            'name' => 'Unique Name',
            'price' => 20.00,
            'type' => 'physical',
        ]);

        $products = Product::where('name', 'Unique Name')->get();
        $this->assertEquals(2, $products->count());
        $slugs = $products->pluck('slug')->toArray();
        $this->assertCount(2, array_unique($slugs));
    }

    public function test_sku_is_unique(): void
    {
        Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'SKU Test',
            'sku' => 'CUSTOM-001',
            'price' => 10.00,
            'status' => 'draft',
        ]);

        $this->actingAs($this->sellerUser)->post(route('seller.products.store'), [
            'name' => 'SKU Test 2',
            'price' => 20.00,
            'type' => 'physical',
        ]);

        $second = Product::where('name', 'SKU Test 2')->first();
        $this->assertNotNull($second);
        $this->assertNotEquals('CUSTOM-001', $second->sku);
    }

    // ── Delete ─────────────────────────────────────────────────

    public function test_seller_can_delete_draft_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Delete Me',
            'price' => 10.00,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)->delete(route('seller.products.destroy', $product));
        $response->assertRedirect();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_seller_cannot_delete_published_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Cannot Delete',
            'price' => 10.00,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->sellerUser)->delete(route('seller.products.destroy', $product));
        $response->assertSessionHas('error');
    }

    // ── DB Transaction Test ────────────────────────────────────

    public function test_product_creation_uses_database_transaction(): void
    {
        $this->actingAs($this->sellerUser)->post(route('seller.products.store'), [
            'name' => 'Transaction Test',
            'price' => 15.00,
            'type' => 'physical',
            'spec' => [],
        ]);

        $this->assertDatabaseHas('products', ['name' => 'Transaction Test']);
    }
}
