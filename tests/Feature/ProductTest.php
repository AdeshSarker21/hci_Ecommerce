<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $sellerUser;
    private Seller $seller;

    protected function setUp(): void
    {
        parent::setUp();

        // Admin permissions
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

        // Seller
        $this->sellerUser = User::factory()->create();
        $this->seller = Seller::factory()->create([
            'user_id' => $this->sellerUser->id,
            'status' => 'approved',
        ]);
    }

    // --- Admin tests ---

    public function test_admin_can_view_products_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertViewIs('admin.products.index');
    }

    public function test_admin_can_view_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Test Product',
            'price' => 19.99,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.show', $product));

        $response->assertOk();
    }

    public function test_admin_can_approve_pending_review_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Pending Product',
            'price' => 29.99,
            'status' => 'pending_review',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.approve', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_approve_rejected_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Rejected Product',
            'price' => 29.99,
            'status' => 'rejected',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.approve', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_cannot_approve_draft_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Draft Product',
            'price' => 29.99,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.approve', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_reject_pending_review_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Pending Product',
            'price' => 29.99,
            'status' => 'pending_review',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.reject', $product), [
                'rejection_reason' => 'Missing images',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'rejected',
            'rejection_reason' => 'Missing images',
        ]);
    }

    public function test_admin_reject_requires_reason(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Pending Product',
            'price' => 29.99,
            'status' => 'pending_review',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.reject', $product), [
                'rejection_reason' => '',
            ]);

        $response->assertSessionHasErrors(['rejection_reason']);
    }

    public function test_admin_can_publish_approved_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Approved Product',
            'price' => 29.99,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.publish', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'published',
        ]);
    }

    public function test_admin_cannot_publish_draft_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Draft Product',
            'price' => 29.99,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.publish', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_unpublish_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Published Product',
            'price' => 29.99,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.unpublish', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_index_filter_by_status(): void
    {
        Product::create(['seller_id' => $this->seller->id, 'name' => 'Draft', 'price' => 10, 'status' => 'draft']);
        Product::create(['seller_id' => $this->seller->id, 'name' => 'Published', 'price' => 10, 'status' => 'published']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.index', ['status' => 'draft']));

        $response->assertOk();
    }

    public function test_admin_index_search(): void
    {
        Product::create(['seller_id' => $this->seller->id, 'name' => 'Widget Pro', 'price' => 10, 'status' => 'published']);
        Product::create(['seller_id' => $this->seller->id, 'name' => 'Gadget Basic', 'price' => 10, 'status' => 'published']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.index', ['search' => 'Widget']));

        $response->assertOk();
    }

    // --- Seller tests ---

    public function test_seller_can_view_products_index(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.products.index'));

        $response->assertOk();
        $response->assertViewIs('seller.products.index');
    }

    public function test_seller_can_view_create_product_form(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.products.create'));

        $response->assertOk();
    }

    public function test_seller_can_store_product(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.store'), [
                'name' => 'New Product',
                'price' => 49.99,
                'type' => 'physical',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'seller_id' => $this->seller->id,
            'name' => 'New Product',
            'price' => 49.99,
            'status' => 'draft',
        ]);
    }

    public function test_seller_store_requires_name_and_price(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.store'), [
                'name' => '',
                'price' => '',
                'type' => '',
            ]);

        $response->assertSessionHasErrors(['name', 'price', 'type']);
    }

    public function test_seller_can_view_own_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'My Product',
            'price' => 19.99,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.products.show', $product));

        $response->assertOk();
    }

    public function test_seller_cannot_view_other_seller_product(): void
    {
        $otherSeller = Seller::factory()->create(['user_id' => User::factory()->create()->id, 'status' => 'approved']);
        $product = Product::create([
            'seller_id' => $otherSeller->id,
            'name' => 'Other Product',
            'price' => 19.99,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.products.show', $product));

        $response->assertForbidden();
    }

    public function test_seller_can_edit_own_draft_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'My Product',
            'price' => 19.99,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.products.edit', $product));

        $response->assertOk();
    }

    public function test_seller_can_update_own_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Old Name',
            'price' => 19.99,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->put(route('seller.products.update', $product), [
                'name' => 'New Name',
                'price' => 29.99,
                'type' => 'physical',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Name',
            'price' => 29.99,
        ]);
    }

    public function test_seller_cannot_edit_published_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Published Product',
            'price' => 19.99,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.products.edit', $product));

        $response->assertRedirect();
    }

    public function test_seller_can_submit_draft_for_review(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Draft Product',
            'price' => 19.99,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.submit', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'pending_review',
        ]);
    }

    public function test_seller_can_submit_rejected_product_for_review(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Rejected Product',
            'price' => 19.99,
            'status' => 'rejected',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.submit', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'pending_review',
        ]);
    }

    public function test_seller_cannot_submit_published_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Published Product',
            'price' => 19.99,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.submit', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'published',
        ]);
    }

    public function test_seller_can_delete_own_draft_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Draft Product',
            'price' => 19.99,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->delete(route('seller.products.destroy', $product));

        $response->assertRedirect(route('seller.products.index'));
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_seller_cannot_delete_published_product(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Published Product',
            'price' => 19.99,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->delete(route('seller.products.destroy', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'published',
        ]);
    }

    public function test_seller_cannot_access_other_seller_product_actions(): void
    {
        $otherSeller = Seller::factory()->create(['user_id' => User::factory()->create()->id, 'status' => 'approved']);
        $product = Product::create([
            'seller_id' => $otherSeller->id,
            'name' => 'Other Product',
            'price' => 19.99,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->put(route('seller.products.update', $product), ['name' => 'Hacked']);

        $response->assertForbidden();
    }

    public function test_unapproved_seller_redirected_from_products(): void
    {
        $unapprovedUser = User::factory()->create();
        Seller::factory()->create([
            'user_id' => $unapprovedUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($unapprovedUser)
            ->get(route('seller.products.index'));

        $response->assertRedirect(route('seller.dashboard'));
    }

    public function test_customer_cannot_access_admin_products(): void
    {
        $customer = User::factory()->create();
        $customerRole = Role::firstOrCreate(['slug' => 'customer', 'name' => 'Customer']);
        $customer->roles()->attach($customerRole);

        $response = $this->actingAs($customer)
            ->get(route('admin.products.index'));

        $response->assertForbidden();
    }

    // --- Product model tests ---

    public function test_product_prevents_duplicate_slugs(): void
    {
        Product::create(['seller_id' => $this->seller->id, 'name' => 'Test Product', 'price' => 10, 'status' => 'draft']);
        $product2 = Product::create(['seller_id' => $this->seller->id, 'name' => 'Test Product', 'price' => 10, 'status' => 'draft']);

        $this->assertNotEquals('test-product', $product2->slug);
        $this->assertStringStartsWith('test-product', $product2->slug);
    }

    public function test_product_auto_generates_sku(): void
    {
        $product = Product::create(['seller_id' => $this->seller->id, 'name' => 'No SKU', 'price' => 10, 'status' => 'draft']);

        $this->assertStringStartsWith('SKU-', $product->sku);
    }

    public function test_product_status_workflow(): void
    {
        $product = Product::create(['seller_id' => $this->seller->id, 'name' => 'Workflow', 'price' => 10, 'status' => 'draft']);

        $this->assertTrue($product->isDraft());
        $this->assertFalse($product->isPendingReview());

        $product->submitForReview();
        $this->assertTrue($product->isPendingReview());

        $product->approve();
        $this->assertTrue($product->isApproved());

        $product->publish();
        $this->assertTrue($product->isPublished());

        $product->unpublish();
        $this->assertTrue($product->isApproved());
    }

    public function test_product_reject_with_reason(): void
    {
        $product = Product::create(['seller_id' => $this->seller->id, 'name' => 'Reject', 'price' => 10, 'status' => 'pending_review']);

        $product->reject('Missing description');

        $this->assertTrue($product->isRejected());
        $this->assertEquals('Missing description', $product->rejection_reason);
    }

    public function test_product_stock_helpers(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Stock Test',
            'price' => 10,
            'status' => 'draft',
            'manage_stock' => true,
            'quantity' => 10,
            'low_stock_threshold' => 5,
        ]);

        $this->assertTrue($product->inStock());
        $this->assertFalse($product->isLowStock());

        $product->update(['quantity' => 3]);
        $this->assertTrue($product->inStock());
        $this->assertTrue($product->isLowStock());

        $product->update(['quantity' => 0]);
        $this->assertFalse($product->inStock());
    }

    public function test_product_unlimited_stock_when_not_managing(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'No Stock Mgmt',
            'price' => 10,
            'status' => 'draft',
            'manage_stock' => false,
            'quantity' => 0,
        ]);

        $this->assertTrue($product->inStock());
        $this->assertFalse($product->isLowStock());
        $this->assertEquals('Unlimited', $product->stock_label);
    }

    public function test_product_discount_percentage(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Discount',
            'price' => 80,
            'compare_at_price' => 100,
            'status' => 'draft',
        ]);

        $this->assertEquals(20.0, $product->discount_percentage);
    }

    public function test_product_discount_zero_when_no_compare_price(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'No Discount',
            'price' => 80,
            'compare_at_price' => null,
            'status' => 'draft',
        ]);

        $this->assertNull($product->discount_percentage);
    }

    public function test_product_badges(): void
    {
        $draft = Product::create(['seller_id' => $this->seller->id, 'name' => 'Draft', 'price' => 10, 'status' => 'draft']);
        $this->assertEquals('bg-gray-100 text-gray-800', $draft->status_badge);

        $pending = Product::create(['seller_id' => $this->seller->id, 'name' => 'Pending', 'price' => 10, 'status' => 'pending_review']);
        $this->assertEquals('bg-yellow-100 text-yellow-800', $pending->status_badge);

        $physical = Product::create(['seller_id' => $this->seller->id, 'name' => 'Physical', 'price' => 10, 'status' => 'draft', 'type' => 'physical']);
        $this->assertEquals('bg-indigo-100 text-indigo-800', $physical->type_badge);
    }

    public function test_product_full_name(): void
    {
        $product = Product::create(['seller_id' => $this->seller->id, 'name' => 'Widget', 'name_bn' => 'উইজেট', 'price' => 10, 'status' => 'draft']);
        $this->assertEquals('Widget (উইজেট)', $product->full_name);

        $product2 = Product::create(['seller_id' => $this->seller->id, 'name' => 'Gadget', 'price' => 10, 'status' => 'draft']);
        $this->assertEquals('Gadget', $product2->full_name);
    }

    public function test_product_scopes(): void
    {
        $otherSeller = Seller::factory()->create(['user_id' => User::factory()->create()->id, 'status' => 'approved']);
        Product::create(['seller_id' => $this->seller->id, 'name' => 'Published', 'price' => 10, 'status' => 'published', 'is_active' => true]);
        Product::create(['seller_id' => $this->seller->id, 'name' => 'Draft', 'price' => 10, 'status' => 'draft', 'is_active' => true]);
        Product::create(['seller_id' => $this->seller->id, 'name' => 'Featured', 'price' => 10, 'status' => 'published', 'is_active' => true, 'is_featured' => true]);
        Product::create(['seller_id' => $otherSeller->id, 'name' => 'Other Seller Published', 'price' => 10, 'status' => 'published', 'is_active' => true]);

        $this->assertEquals(3, Product::active()->count());
        $this->assertEquals(1, Product::byStatus('draft')->count());
        $this->assertEquals(3, Product::bySeller($this->seller->id)->count());
        $this->assertEquals(1, Product::featured()->count());
    }
}
