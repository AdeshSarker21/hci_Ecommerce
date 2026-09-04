<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Seller;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Seller $seller;
    protected User $sellerUser;

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

    // ── Admin Tests ────────────────────────────────────────────

    public function test_admin_can_view_warehouses_index(): void
    {
        Warehouse::factory()->count(3)->create();
        $response = $this->actingAs($this->admin)->get(route('admin.warehouses.index'));
        $response->assertStatus(200)->assertViewIs('admin.warehouses.index');
    }

    public function test_admin_can_create_warehouse(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.warehouses.store'), [
            'name' => 'Main Warehouse',
            'code' => 'WH-001',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('warehouses', ['code' => 'WH-001']);
    }

    public function test_admin_can_update_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create(['code' => 'WH-OLD']);

        $response = $this->actingAs($this->admin)->put(route('admin.warehouses.update', $warehouse), [
            'name' => 'Updated Name',
            'code' => 'WH-NEW',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('warehouses', ['code' => 'WH-NEW']);
    }

    public function test_admin_can_delete_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.warehouses.destroy', $warehouse));

        $response->assertRedirect();
        $this->assertSoftDeleted('warehouses', ['id' => $warehouse->id]);
    }

    public function test_admin_can_toggle_warehouse_status(): void
    {
        $warehouse = Warehouse::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)->post(route('admin.warehouses.toggle-status', $warehouse));

        $response->assertRedirect();
        $this->assertDatabaseHas('warehouses', ['id' => $warehouse->id, 'status' => 'inactive']);
    }

    public function test_admin_warehouse_code_must_be_unique(): void
    {
        Warehouse::factory()->create(['code' => 'WH-001']);

        $response = $this->actingAs($this->admin)->post(route('admin.warehouses.store'), [
            'name' => 'Duplicate',
            'code' => 'WH-001',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('code');
    }

    // ── Seller Tests ───────────────────────────────────────────

    public function test_seller_can_view_warehouses(): void
    {
        Warehouse::factory()->forSeller($this->seller)->create();
        $response = $this->actingAs($this->sellerUser)->get(route('seller.warehouses.index'));
        $response->assertStatus(200)->assertViewIs('seller.warehouses.index');
    }

    public function test_seller_can_create_warehouse(): void
    {
        $response = $this->actingAs($this->sellerUser)->post(route('seller.warehouses.store'), [
            'name' => 'Seller Warehouse',
            'code' => 'SW-001',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('warehouses', ['code' => 'SW-001', 'seller_id' => $this->seller->id]);
    }

    public function test_seller_can_update_own_warehouse(): void
    {
        $warehouse = Warehouse::factory()->forSeller($this->seller)->create(['code' => 'SW-OLD']);

        $response = $this->actingAs($this->sellerUser)->put(route('seller.warehouses.update', $warehouse), [
            'name' => 'Updated',
            'code' => 'SW-NEW',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('warehouses', ['code' => 'SW-NEW']);
    }

    public function test_seller_cannot_update_other_seller_warehouse(): void
    {
        $otherSeller = Seller::factory()->create(['user_id' => User::factory()->create()->id]);
        $warehouse = Warehouse::factory()->forSeller($otherSeller)->create();

        $response = $this->actingAs($this->sellerUser)->put(route('seller.warehouses.update', $warehouse), [
            'name' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_seller_can_delete_own_warehouse(): void
    {
        $warehouse = Warehouse::factory()->forSeller($this->seller)->create();

        $response = $this->actingAs($this->sellerUser)->delete(route('seller.warehouses.destroy', $warehouse));

        $response->assertRedirect();
        $this->assertSoftDeleted('warehouses', ['id' => $warehouse->id]);
    }

    public function test_unauthenticated_cannot_access_warehouses(): void
    {
        Warehouse::factory()->create();
        $response = $this->get(route('admin.warehouses.index'));
        $response->assertRedirect('/login');
    }
}
