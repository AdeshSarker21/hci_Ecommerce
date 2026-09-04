<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private User $sellerUser;
    private Role $sellerRole;

    protected function setUp(): void
    {
        parent::setUp();

        $managePerm = Permission::firstOrCreate(['slug' => 'vendors.manage'], ['name' => 'vendors.manage', 'group' => 'vendors']);
        Permission::firstOrCreate(['slug' => 'vendors.view'], ['name' => 'vendors.view', 'group' => 'vendors']);

        $adminRole = Role::firstOrCreate(['slug' => 'admin', 'name' => 'Admin']);
        $this->sellerRole = Role::firstOrCreate(['slug' => 'seller', 'name' => 'Seller']);

        $adminRole->permissions()->syncWithoutDetaching([$managePerm->id]);

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);

        $this->customer = User::factory()->create();

        $this->sellerUser = User::factory()->create();
        $this->sellerUser->roles()->attach($this->sellerRole);
    }

    public function test_customer_can_register_as_seller(): void
    {
        $response = $this->actingAs($this->customer)
            ->post(route('seller.register.store'), [
                'store_name' => 'My Test Store',
                'store_description' => 'A great store',
            ]);

        $response->assertRedirect(route('seller.dashboard'));
        $this->assertDatabaseHas('sellers', [
            'user_id' => $this->customer->id,
            'store_name' => 'My Test Store',
            'status' => 'pending',
        ]);
    }

    public function test_seller_registration_requires_store_name(): void
    {
        $response = $this->actingAs($this->customer)
            ->post(route('seller.register.store'), [
                'store_name' => '',
            ]);

        $response->assertSessionHasErrors('store_name');
    }

    public function test_seller_cannot_register_twice(): void
    {
        Seller::factory()->create(['user_id' => $this->customer->id, 'status' => 'pending']);

        $response = $this->actingAs($this->customer)
            ->post(route('seller.register.store'), [
                'store_name' => 'Another Store',
            ]);

        $response->assertRedirect(route('seller.dashboard'));
    }

    public function test_pending_seller_sees_dashboard(): void
    {
        Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'pending']);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.dashboard'));

        $response->assertOk();
        $response->assertSee('Application Under Review');
    }

    public function test_approved_seller_sees_dashboard(): void
    {
        Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'approved']);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.dashboard'));

        $response->assertOk();
    }

    public function test_admin_can_view_sellers_index(): void
    {
        Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.sellers.index'));

        $response->assertOk();
    }

    public function test_admin_can_view_seller(): void
    {
        $seller = Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.sellers.show', $seller));

        $response->assertOk();
        $response->assertSee($seller->store_name);
    }

    public function test_admin_can_approve_pending_seller(): void
    {
        $seller = Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sellers.approve', $seller));

        $response->assertRedirect();
        $this->assertDatabaseHas('sellers', ['id' => $seller->id, 'status' => 'approved']);
    }

    public function test_admin_cannot_approve_already_approved_seller(): void
    {
        $seller = Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'approved']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sellers.approve', $seller));

        $response->assertRedirect();
        $this->assertDatabaseHas('sellers', ['id' => $seller->id, 'status' => 'approved']);
    }

    public function test_admin_can_reject_pending_seller(): void
    {
        $seller = Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sellers.reject', $seller), [
                'rejection_reason' => 'Not meeting requirements',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sellers', ['id' => $seller->id, 'status' => 'rejected']);
    }

    public function test_rejection_requires_reason(): void
    {
        $seller = Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sellers.reject', $seller), [
                'rejection_reason' => '',
            ]);

        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_admin_can_suspend_approved_seller(): void
    {
        $seller = Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'approved']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sellers.suspend', $seller), [
                'rejection_reason' => 'Policy violation',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sellers', ['id' => $seller->id, 'status' => 'suspended']);
    }

    public function test_seller_cannot_approve_own_seller(): void
    {
        $seller = Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'pending']);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('admin.sellers.approve', $seller));

        $response->assertForbidden();
    }

    public function test_unauthenticated_cannot_access_seller_routes(): void
    {
        $response = $this->get(route('seller.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_seller_can_edit_profile(): void
    {
        Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'approved']);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.profile.edit'));

        $response->assertOk();
    }

    public function test_pending_seller_cannot_edit_profile(): void
    {
        Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'pending']);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.profile.edit'));

        $response->assertRedirect(route('seller.dashboard'));
    }

    public function test_seller_can_update_profile(): void
    {
        $seller = Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'approved']);

        $response = $this->actingAs($this->sellerUser)
            ->put(route('seller.profile.update'), [
                'store_name' => 'Updated Store Name',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sellers', ['id' => $seller->id, 'store_name' => 'Updated Store Name']);
    }

    public function test_seller_index_filters_by_status(): void
    {
        Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'approved']);
        $pendingUser = User::factory()->create();
        Seller::factory()->create(['user_id' => $pendingUser->id, 'status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.sellers.index', ['status' => 'approved']));

        $response->assertOk();
    }

    public function test_user_without_permission_cannot_access_admin_sellers(): void
    {
        $basicUser = User::factory()->create();
        $basicRole = Role::firstOrCreate(['slug' => 'customer', 'name' => 'Customer']);
        $basicUser->roles()->attach($basicRole);

        $response = $this->actingAs($basicUser)
            ->get(route('admin.sellers.index'));

        $response->assertForbidden();
    }

    public function test_seller_user_model_has_seller_relationship(): void
    {
        Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'approved']);

        $this->assertTrue($this->sellerUser->seller->exists());
    }

    public function test_user_is_seller_helper(): void
    {
        Seller::factory()->create(['user_id' => $this->sellerUser->id, 'status' => 'approved']);

        $this->assertTrue($this->sellerUser->isSeller());
    }
}
