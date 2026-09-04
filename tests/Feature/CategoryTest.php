<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $productManager;

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

        $pmRole = Role::firstOrCreate(['slug' => 'product-manager', 'name' => 'Product Manager']);
        $pmRole->permissions()->sync(
            Permission::whereIn('slug', ['products.view', 'products.create', 'products.update'])->pluck('id')
        );

        $this->productManager = User::factory()->create();
        $this->productManager->roles()->attach($pmRole);
    }

    public function test_admin_can_view_categories_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.index'));

        $response->assertOk();
        $response->assertViewIs('admin.categories.index');
    }

    public function test_admin_can_create_category(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Electronics',
                'name_bn' => 'ইলেকট্রনিক্স',
                'description' => 'Electronic devices',
                'status' => 'active',
                'sort_order' => 1,
            ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
            'name_bn' => 'ইলেকট্রনিক্স',
            'slug' => 'electronics',
            'status' => 'active',
        ]);
    }

    public function test_category_requires_name(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name' => '',
                'status' => '',
            ]);

        $response->assertSessionHasErrors(['name', 'status']);
    }

    public function test_admin_can_create_child_category(): void
    {
        $parent = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Smartphones',
                'parent_id' => $parent->id,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.categories.index'));

        $child = Category::where('slug', 'smartphones')->first();
        $this->assertNotNull($child);
        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertEquals(1, $child->depth);
    }

    public function test_admin_can_view_category(): void
    {
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.show', $category));

        $response->assertOk();
    }

    public function test_admin_can_edit_category(): void
    {
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.edit', $category));

        $response->assertOk();
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'Electronic Devices',
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Electronic Devices',
        ]);
    }

    public function test_admin_can_toggle_category_status(): void
    {
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.toggle-status', $category));

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_delete_category(): void
    {
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_deleting_parent_reassigns_children(): void
    {
        $parent = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 'active',
        ]);

        $child = Category::create([
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'parent_id' => $parent->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $parent));

        $this->assertSoftDeleted('categories', ['id' => $parent->id]);
        $this->assertDatabaseHas('categories', [
            'id' => $child->id,
            'parent_id' => null,
        ]);
    }

    public function test_category_prevents_duplicate_slugs(): void
    {
        Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 'active',
        ]);

        $category2 = Category::create([
            'name' => 'Electronics',
            'status' => 'active',
        ]);

        $this->assertNotEquals('electronics', $category2->slug);
        $this->assertStringStartsWith('electronics', $category2->slug);
    }

    public function test_product_manager_can_view_categories(): void
    {
        $response = $this->actingAs($this->productManager)
            ->get(route('admin.categories.index'));

        $response->assertOk();
    }

    public function test_customer_cannot_access_categories(): void
    {
        $customer = User::factory()->create();
        $customerRole = Role::firstOrCreate(['slug' => 'customer', 'name' => 'Customer']);
        $customer->roles()->attach($customerRole);

        $response = $this->actingAs($customer)
            ->get(route('admin.categories.index'));

        $response->assertForbidden();
    }

    public function test_category_scopes(): void
    {
        Category::create(['name' => 'Active', 'slug' => 'active-cat', 'status' => 'active']);
        Category::create(['name' => 'Inactive', 'slug' => 'inactive-cat', 'status' => 'inactive']);
        Category::create(['name' => 'Root', 'slug' => 'root-cat', 'status' => 'active']);

        $this->assertEquals(2, Category::active()->count());
        $this->assertEquals(3, Category::roots()->count());
    }

    public function test_category_breadcrumbs(): void
    {
        $root = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'status' => 'active']);
        $child = Category::create(['name' => 'Phones', 'slug' => 'phones', 'parent_id' => $root->id, 'status' => 'active']);

        $this->assertEquals('Electronics > Phones', $child->breadcrumbs);
    }
}
