<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Seller;
use App\Models\SellerStaff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerStaffTest extends TestCase
{
    use RefreshDatabase;

    private User $sellerUser;
    private User $admin;
    private Seller $seller;
    private Role $sellerRole;

    protected function setUp(): void
    {
        parent::setUp();

        $managePerm = Permission::firstOrCreate(['slug' => 'vendors.manage'], ['name' => 'vendors.manage', 'group' => 'vendors']);
        Permission::firstOrCreate(['slug' => 'vendors.view'], ['name' => 'vendors.view', 'group' => 'vendors']);
        Permission::firstOrCreate(['slug' => 'products.view'], ['name' => 'products.view', 'group' => 'products']);

        $adminRole = Role::firstOrCreate(['slug' => 'admin', 'name' => 'Admin']);
        $this->sellerRole = Role::firstOrCreate(['slug' => 'seller', 'name' => 'Seller']);
        Role::firstOrCreate(['slug' => 'seller-staff', 'name' => 'Seller Staff']);

        $adminRole->permissions()->syncWithoutDetaching([$managePerm->id]);

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);

        $this->sellerUser = User::factory()->create();
        $this->sellerUser->roles()->attach($this->sellerRole);

        $this->seller = Seller::factory()->create([
            'user_id' => $this->sellerUser->id,
            'status' => 'approved',
        ]);
    }

    public function test_seller_can_view_staff_index(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.staff.index'));

        $response->assertOk();
        $response->assertViewIs('seller.staff.index');
    }

    public function test_unapproved_seller_cannot_access_staff(): void
    {
        $pendingSeller = User::factory()->create();
        $pendingSeller->roles()->attach($this->sellerRole);
        Seller::factory()->create(['user_id' => $pendingSeller->id, 'status' => 'pending']);

        $response = $this->actingAs($pendingSeller)
            ->get(route('seller.staff.index'));

        $response->assertRedirect(route('seller.dashboard'));
    }

    public function test_seller_can_view_create_staff_form(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.staff.create'));

        $response->assertOk();
        $response->assertViewIs('seller.staff.create');
    }

    public function test_seller_can_create_staff(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.staff.store'), [
                'name' => 'John Staff',
                'email' => 'john@example.com',
                'role' => 'staff',
                'can_manage_products' => true,
                'can_manage_orders' => false,
                'can_manage_settings' => false,
                'can_view_reports' => true,
            ]);

        $response->assertRedirect(route('seller.staff.index'));
        $this->assertDatabaseHas('seller_staff', [
            'seller_id' => $this->seller->id,
            'name' => 'John Staff',
            'email' => 'john@example.com',
            'role' => 'staff',
            'can_manage_products' => true,
            'can_manage_orders' => false,
            'can_view_reports' => true,
        ]);
    }

    public function test_staff_creation_requires_name_and_email(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.staff.store'), [
                'name' => '',
                'email' => '',
                'role' => '',
            ]);

        $response->assertSessionHasErrors(['name', 'email', 'role']);
    }

    public function test_seller_can_view_staff(): void
    {
        $staff = SellerStaff::factory()->create([
            'seller_id' => $this->seller->id,
            'name' => 'Test Staff',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.staff.show', $staff));

        $response->assertOk();
    }

    public function test_seller_cannot_view_other_sellers_staff(): void
    {
        $otherSeller = User::factory()->create();
        $otherSeller->roles()->attach($this->sellerRole);
        $otherSellerModel = Seller::factory()->create(['user_id' => $otherSeller->id, 'status' => 'approved']);

        $staff = SellerStaff::factory()->create([
            'seller_id' => $otherSellerModel->id,
            'name' => 'Other Staff',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.staff.show', $staff));

        $response->assertForbidden();
    }

    public function test_seller_can_edit_staff(): void
    {
        $staff = SellerStaff::factory()->create([
            'seller_id' => $this->seller->id,
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.staff.edit', $staff));

        $response->assertOk();
    }

    public function test_seller_can_update_staff(): void
    {
        $staff = SellerStaff::factory()->create([
            'seller_id' => $this->seller->id,
            'name' => 'Original Name',
            'role' => 'staff',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->put(route('seller.staff.update', $staff), [
                'name' => 'Updated Name',
                'role' => 'manager',
                'can_manage_products' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('seller_staff', [
            'id' => $staff->id,
            'name' => 'Updated Name',
            'role' => 'manager',
        ]);
    }

    public function test_seller_can_toggle_staff_status(): void
    {
        $staff = SellerStaff::factory()->create([
            'seller_id' => $this->seller->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.staff.toggle-status', $staff));

        $response->assertRedirect();
        $this->assertDatabaseHas('seller_staff', [
            'id' => $staff->id,
            'is_active' => false,
        ]);
    }

    public function test_seller_can_delete_staff(): void
    {
        $staff = SellerStaff::factory()->create([
            'seller_id' => $this->seller->id,
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->delete(route('seller.staff.destroy', $staff));

        $response->assertRedirect(route('seller.staff.index'));
        $this->assertDatabaseMissing('seller_staff', ['id' => $staff->id]);
    }

    public function test_seller_staff_model_relationships(): void
    {
        $staff = SellerStaff::factory()->create([
            'seller_id' => $this->seller->id,
        ]);

        $this->assertNotNull($staff->seller);
        $this->assertEquals($this->seller->id, $staff->seller_id);
    }

    public function test_staff_permissions_array(): void
    {
        $staff = SellerStaff::factory()->create([
            'seller_id' => $this->seller->id,
            'can_manage_products' => true,
            'can_manage_orders' => true,
            'can_manage_settings' => false,
            'can_view_reports' => true,
        ]);

        $permissions = $staff->getPermissionsArray();
        $this->assertArrayHasKey('products', $permissions);
        $this->assertArrayHasKey('orders', $permissions);
        $this->assertArrayNotHasKey('settings', $permissions);
        $this->assertArrayHasKey('reports', $permissions);
    }

    public function test_non_seller_cannot_access_staff_routes(): void
    {
        $customer = User::factory()->create();
        $customerRole = Role::firstOrCreate(['slug' => 'customer', 'name' => 'Customer']);
        $customer->roles()->attach($customerRole);

        $response = $this->actingAs($customer)
            ->get(route('seller.staff.index'));

        $response->assertRedirect(route('seller.dashboard'));
    }
}
