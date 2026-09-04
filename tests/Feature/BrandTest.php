<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

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
    }

    public function test_admin_can_view_brands_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.brands.index'));

        $response->assertOk();
        $response->assertViewIs('admin.brands.index');
    }

    public function test_admin_can_create_brand(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.brands.store'), [
                'name' => 'Samsung',
                'name_bn' => 'স্যামসাং',
                'description' => 'Samsung Electronics',
                'status' => 'active',
                'sort_order' => 1,
            ]);

        $response->assertRedirect(route('admin.brands.index'));
        $this->assertDatabaseHas('brands', [
            'name' => 'Samsung',
            'name_bn' => 'স্যামসাং',
            'slug' => 'samsung',
            'status' => 'active',
        ]);
    }

    public function test_brand_requires_name(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.brands.store'), [
                'name' => '',
                'status' => '',
            ]);

        $response->assertSessionHasErrors(['name', 'status']);
    }

    public function test_admin_can_view_brand(): void
    {
        $brand = Brand::create([
            'name' => 'Samsung',
            'slug' => 'samsung',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.brands.show', $brand));

        $response->assertOk();
    }

    public function test_admin_can_edit_brand(): void
    {
        $brand = Brand::create([
            'name' => 'Samsung',
            'slug' => 'samsung',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.brands.edit', $brand));

        $response->assertOk();
    }

    public function test_admin_can_update_brand(): void
    {
        $brand = Brand::create([
            'name' => 'Samsung',
            'slug' => 'samsung',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.brands.update', $brand), [
                'name' => 'Samsung Electronics',
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.brands.index'));
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'Samsung Electronics',
        ]);
    }

    public function test_admin_can_toggle_brand_status(): void
    {
        $brand = Brand::create([
            'name' => 'Samsung',
            'slug' => 'samsung',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.brands.toggle-status', $brand));

        $response->assertRedirect();
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_delete_brand(): void
    {
        $brand = Brand::create([
            'name' => 'Samsung',
            'slug' => 'samsung',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.brands.destroy', $brand));

        $response->assertRedirect(route('admin.brands.index'));
        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }

    public function test_brand_prevents_duplicate_slugs(): void
    {
        Brand::create([
            'name' => 'Samsung',
            'slug' => 'samsung',
            'status' => 'active',
        ]);

        $brand2 = Brand::create([
            'name' => 'Samsung',
            'status' => 'active',
        ]);

        $this->assertNotEquals('samsung', $brand2->slug);
        $this->assertStringStartsWith('samsung', $brand2->slug);
    }

    public function test_customer_cannot_access_brands(): void
    {
        $customer = User::factory()->create();
        $customerRole = Role::firstOrCreate(['slug' => 'customer', 'name' => 'Customer']);
        $customer->roles()->attach($customerRole);

        $response = $this->actingAs($customer)
            ->get(route('admin.brands.index'));

        $response->assertForbidden();
    }

    public function test_brand_status_badge(): void
    {
        $active = Brand::create(['name' => 'A', 'slug' => 'a', 'status' => 'active']);
        $inactive = Brand::create(['name' => 'B', 'slug' => 'b', 'status' => 'inactive']);

        $this->assertEquals('bg-green-100 text-green-800', $active->status_badge);
        $this->assertEquals('bg-red-100 text-red-800', $inactive->status_badge);
    }

    public function test_brand_is_active_helper(): void
    {
        $brand = Brand::create(['name' => 'A', 'slug' => 'a', 'status' => 'active']);
        $this->assertTrue($brand->isActive());

        $brand->update(['status' => 'inactive']);
        $this->assertFalse($brand->isActive());
    }

    public function test_brand_full_name_with_bn(): void
    {
        $brand = Brand::create(['name' => 'Samsung', 'name_bn' => 'স্যামসাং', 'slug' => 'samsung', 'status' => 'active']);
        $this->assertEquals('Samsung (স্যামসাং)', $brand->full_name);
    }

    public function test_brand_full_name_without_bn(): void
    {
        $brand = Brand::create(['name' => 'Samsung', 'slug' => 'samsung', 'status' => 'active']);
        $this->assertEquals('Samsung', $brand->full_name);
    }

    public function test_brand_index_filter_by_status(): void
    {
        Brand::create(['name' => 'Active', 'slug' => 'active-brand', 'status' => 'active']);
        Brand::create(['name' => 'Inactive', 'slug' => 'inactive-brand', 'status' => 'inactive']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.brands.index', ['status' => 'active']));

        $response->assertOk();
    }

    public function test_brand_index_search(): void
    {
        Brand::create(['name' => 'Samsung', 'slug' => 'samsung', 'status' => 'active']);
        Brand::create(['name' => 'Apple', 'slug' => 'apple', 'status' => 'active']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.brands.index', ['search' => 'Samsung']));

        $response->assertOk();
    }
}
