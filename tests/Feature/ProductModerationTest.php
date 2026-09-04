<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductModeration;
use App\Models\Role;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModerationTest extends TestCase
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

    private function createProduct(string $status = 'draft'): Product
    {
        return Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Test Product',
            'price' => 29.99,
            'status' => $status,
        ]);
    }

    // --- Moderation Queue Index ---

    public function test_admin_can_view_review_queue(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.review.index'));

        $response->assertOk();
        $response->assertViewIs('admin.review.index');
    }

    public function test_review_queue_shows_pending_review_products(): void
    {
        $product = $this->createProduct('pending_review');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.review.index', ['status' => 'pending_review']));

        $response->assertOk();
        $response->assertSee($product->name);
    }

    public function test_review_queue_filters_by_seller(): void
    {
        $product = $this->createProduct('pending_review');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.review.index', ['seller_id' => $this->seller->id]));

        $response->assertOk();
        $response->assertSee($product->name);
    }

    public function test_review_queue_filters_by_category(): void
    {
        $category = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'is_active' => true]);
        $product = $this->createProduct('pending_review');
        $product->update(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.review.index', ['category_id' => $category->id]));

        $response->assertOk();
        $response->assertSee($product->name);
    }

    public function test_review_queue_filters_by_date_range(): void
    {
        $product = $this->createProduct('pending_review');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.review.index', [
                'from_date' => now()->subDay()->toDateString(),
                'to_date' => now()->addDay()->toDateString(),
            ]));

        $response->assertOk();
        $response->assertSee($product->name);
    }

    // --- Review Show Page ---

    public function test_admin_can_view_review_detail(): void
    {
        $product = $this->createProduct('pending_review');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.review.show', $product));

        $response->assertOk();
        $response->assertViewIs('admin.review.show');
        $response->assertSee($product->name);
    }

    // --- Approve from Review Queue ---

    public function test_admin_can_approve_from_review_queue(): void
    {
        $product = $this->createProduct('pending_review');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.review.approve', $product));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $product->refresh();
        $this->assertEquals('approved', $product->status);
        $this->assertNotNull($product->approved_at);
    }

    public function test_approve_logs_moderation_history(): void
    {
        $product = $this->createProduct('pending_review');

        $this->actingAs($this->admin)
            ->post(route('admin.review.approve', $product));

        $this->assertDatabaseHas('product_moderations', [
            'product_id' => $product->id,
            'reviewer_id' => $this->admin->id,
            'action' => 'approve',
            'previous_status' => 'pending_review',
            'new_status' => 'approved',
        ]);
    }

    public function test_approve_sends_notification_to_seller(): void
    {
        $product = $this->createProduct('pending_review');

        $this->actingAs($this->admin)
            ->post(route('admin.review.approve', $product));

        $this->assertDatabaseHas('notifications', [
            'type' => \App\Notifications\ProductStatusChanged::class,
            'notifiable_id' => $this->sellerUser->id,
        ]);
    }

    public function test_admin_can_approve_rejected_product(): void
    {
        $product = $this->createProduct('rejected');

        $this->actingAs($this->admin)
            ->post(route('admin.review.approve', $product));

        $product->refresh();
        $this->assertEquals('approved', $product->status);
    }

    public function test_cannot_approve_draft_product(): void
    {
        $product = $this->createProduct('draft');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.review.approve', $product));

        $response->assertSessionHas('error');
        $product->refresh();
        $this->assertEquals('draft', $product->status);
    }

    public function test_cannot_approve_published_product(): void
    {
        $product = $this->createProduct('published');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.review.approve', $product));

        $response->assertSessionHas('error');
    }

    // --- Reject from Review Queue ---

    public function test_admin_can_reject_from_review_queue(): void
    {
        $product = $this->createProduct('pending_review');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.review.reject', $product), [
                'rejection_reason' => 'Missing required images',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $product->refresh();
        $this->assertEquals('rejected', $product->status);
        $this->assertEquals('Missing required images', $product->rejection_reason);
    }

    public function test_reject_logs_moderation_history(): void
    {
        $product = $this->createProduct('pending_review');

        $this->actingAs($this->admin)
            ->post(route('admin.review.reject', $product), [
                'rejection_reason' => 'Poor quality images',
            ]);

        $this->assertDatabaseHas('product_moderations', [
            'product_id' => $product->id,
            'reviewer_id' => $this->admin->id,
            'action' => 'reject',
            'previous_status' => 'pending_review',
            'new_status' => 'rejected',
            'reason' => 'Poor quality images',
        ]);
    }

    public function test_reject_sends_notification_to_seller(): void
    {
        $product = $this->createProduct('pending_review');

        $this->actingAs($this->admin)
            ->post(route('admin.review.reject', $product), [
                'rejection_reason' => 'Incomplete description',
            ]);

        $this->assertDatabaseHas('notifications', [
            'type' => \App\Notifications\ProductStatusChanged::class,
            'notifiable_id' => $this->sellerUser->id,
        ]);
    }

    public function test_reject_requires_reason(): void
    {
        $product = $this->createProduct('pending_review');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.review.reject', $product));

        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_admin_can_reject_approved_product(): void
    {
        $product = $this->createProduct('approved');

        $this->actingAs($this->admin)
            ->post(route('admin.review.reject', $product), [
                'rejection_reason' => 'Changed our mind',
            ]);

        $product->refresh();
        $this->assertEquals('rejected', $product->status);
    }

    // --- Publish from Review Queue ---

    public function test_admin_can_publish_from_review_queue(): void
    {
        $product = $this->createProduct('approved');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.review.publish', $product));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $product->refresh();
        $this->assertEquals('published', $product->status);
        $this->assertNotNull($product->published_at);
    }

    public function test_publish_logs_moderation_history(): void
    {
        $product = $this->createProduct('approved');

        $this->actingAs($this->admin)
            ->post(route('admin.review.publish', $product));

        $this->assertDatabaseHas('product_moderations', [
            'product_id' => $product->id,
            'reviewer_id' => $this->admin->id,
            'action' => 'publish',
            'previous_status' => 'approved',
            'new_status' => 'published',
        ]);
    }

    public function test_publish_sends_notification_to_seller(): void
    {
        $product = $this->createProduct('approved');

        $this->actingAs($this->admin)
            ->post(route('admin.review.publish', $product));

        $this->assertDatabaseHas('notifications', [
            'type' => \App\Notifications\ProductStatusChanged::class,
            'notifiable_id' => $this->sellerUser->id,
        ]);
    }

    public function test_cannot_publish_pending_review_product(): void
    {
        $product = $this->createProduct('pending_review');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.review.publish', $product));

        $response->assertSessionHas('error');
    }

    // --- Unpublish from Review Queue ---

    public function test_admin_can_unpublish_from_review_queue(): void
    {
        $product = $this->createProduct('published');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.review.unpublish', $product));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $product->refresh();
        $this->assertEquals('approved', $product->status);
        $this->assertNull($product->published_at);
    }

    public function test_unpublish_logs_moderation_history(): void
    {
        $product = $this->createProduct('published');

        $this->actingAs($this->admin)
            ->post(route('admin.review.unpublish', $product));

        $this->assertDatabaseHas('product_moderations', [
            'product_id' => $product->id,
            'reviewer_id' => $this->admin->id,
            'action' => 'unpublish',
            'previous_status' => 'published',
            'new_status' => 'approved',
        ]);
    }

    public function test_cannot_unpublish_draft_product(): void
    {
        $product = $this->createProduct('draft');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.review.unpublish', $product));

        $response->assertSessionHas('error');
    }

    // --- Seller Submit for Review ---

    public function test_seller_submit_for_review_logs_moderation(): void
    {
        $product = $this->createProduct('draft');

        $this->actingAs($this->sellerUser)
            ->post(route('seller.products.submit', $product));

        $this->assertDatabaseHas('product_moderations', [
            'product_id' => $product->id,
            'reviewer_id' => $this->sellerUser->id,
            'action' => 'submit_for_review',
            'previous_status' => 'draft',
            'new_status' => 'pending_review',
        ]);
    }

    public function test_seller_can_resubmit_rejected_product(): void
    {
        $product = $this->createProduct('rejected');

        $this->actingAs($this->sellerUser)
            ->post(route('seller.products.submit', $product));

        $product->refresh();
        $this->assertEquals('pending_review', $product->status);
    }

    // --- Moderation History on Product ---

    public function test_product_moderation_history_is_logged(): void
    {
        $product = $this->createProduct('draft');

        // Submit
        $this->actingAs($this->sellerUser)
            ->post(route('seller.products.submit', $product));

        // Approve
        $this->actingAs($this->admin)
            ->post(route('admin.review.approve', $product));

        // Publish
        $this->actingAs($this->admin)
            ->post(route('admin.review.publish', $product));

        $this->assertEquals(3, $product->moderations()->count());
    }

    public function test_review_shows_moderation_history(): void
    {
        $product = $this->createProduct('pending_review');

        $this->actingAs($this->admin)
            ->post(route('admin.review.approve', $product));

        $response = $this->actingAs($this->admin)
            ->get(route('admin.review.show', $product));

        $response->assertOk();
        $response->assertSee('Moderation History');
        $response->assertSee('Approved');
    }

    // --- Authorization ---

    public function test_unauthorized_user_cannot_access_review_queue(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.review.index'));

        $response->assertForbidden();
    }

    public function test_unauthorized_user_cannot_approve_product(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct('pending_review');

        $response = $this->actingAs($user)
            ->post(route('admin.review.approve', $product));

        $response->assertForbidden();
    }

    // --- Product Status Badges ---

    public function test_product_moderation_model_has_action_label(): void
    {
        $moderation = ProductModeration::create([
            'product_id' => $this->createProduct()->id,
            'reviewer_id' => $this->admin->id,
            'action' => 'approve',
            'previous_status' => 'pending_review',
            'new_status' => 'approved',
        ]);

        $this->assertEquals('Approved', $moderation->action_label);
    }

    public function test_product_moderation_model_has_action_badge(): void
    {
        $moderation = ProductModeration::create([
            'product_id' => $this->createProduct()->id,
            'reviewer_id' => $this->admin->id,
            'action' => 'reject',
            'previous_status' => 'pending_review',
            'new_status' => 'rejected',
        ]);

        $this->assertEquals('bg-red-100 text-red-800', $moderation->action_badge);
    }
}
