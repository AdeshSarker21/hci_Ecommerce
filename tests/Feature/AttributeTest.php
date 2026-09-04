<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeTest extends TestCase
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

    public function test_admin_can_view_attributes_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.attributes.index'));

        $response->assertOk();
        $response->assertViewIs('admin.attributes.index');
    }

    public function test_admin_can_create_attribute(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.attributes.store'), [
                'name' => 'Color',
                'name_bn' => 'রঙ',
                'type' => 'select',
                'is_required' => true,
                'is_filterable' => true,
                'is_variant' => true,
                'status' => 'active',
                'sort_order' => 1,
                'values' => [
                    ['value' => 'Red', 'value_bn' => 'লাল', 'color_code' => '#FF0000'],
                    ['value' => 'Blue', 'value_bn' => 'নীল', 'color_code' => '#0000FF'],
                ],
            ]);

        $response->assertRedirect(route('admin.attributes.index'));
        $this->assertDatabaseHas('attributes', [
            'name' => 'Color',
            'name_bn' => 'রঙ',
            'slug' => 'color',
            'type' => 'select',
            'is_required' => true,
            'is_filterable' => true,
            'is_variant' => true,
        ]);

        $this->assertDatabaseHas('attribute_values', [
            'attribute_id' => Attribute::where('slug', 'color')->first()->id,
            'value' => 'Red',
            'value_bn' => 'লাল',
            'color_code' => '#FF0000',
        ]);

        $this->assertDatabaseHas('attribute_values', [
            'attribute_id' => Attribute::where('slug', 'color')->first()->id,
            'value' => 'Blue',
            'value_bn' => 'নীল',
            'color_code' => '#0000FF',
        ]);
    }

    public function test_attribute_requires_name_and_type(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.attributes.store'), [
                'name' => '',
                'type' => '',
                'status' => '',
            ]);

        $response->assertSessionHasErrors(['name', 'type', 'status']);
    }

    public function test_admin_can_view_attribute(): void
    {
        $attribute = Attribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.attributes.show', $attribute));

        $response->assertOk();
    }

    public function test_admin_can_edit_attribute(): void
    {
        $attribute = Attribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.attributes.edit', $attribute));

        $response->assertOk();
    }

    public function test_admin_can_update_attribute(): void
    {
        $attribute = Attribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.attributes.update', $attribute), [
                'name' => 'Colour',
                'type' => 'select',
                'status' => 'active',
                'values' => [
                    ['value' => 'Red'],
                ],
            ]);

        $response->assertRedirect(route('admin.attributes.index'));
        $this->assertDatabaseHas('attributes', [
            'id' => $attribute->id,
            'name' => 'Colour',
        ]);
    }

    public function test_admin_can_toggle_attribute_status(): void
    {
        $attribute = Attribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.attributes.toggle-status', $attribute));

        $response->assertRedirect();
        $this->assertDatabaseHas('attributes', [
            'id' => $attribute->id,
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_delete_attribute(): void
    {
        $attribute = Attribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.attributes.destroy', $attribute));

        $response->assertRedirect(route('admin.attributes.index'));
        $this->assertSoftDeleted('attributes', ['id' => $attribute->id]);
    }

    public function test_attribute_prevents_duplicate_slugs(): void
    {
        Attribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'status' => 'active',
        ]);

        $attr2 = Attribute::create([
            'name' => 'Color',
            'type' => 'select',
            'status' => 'active',
        ]);

        $this->assertNotEquals('color', $attr2->slug);
        $this->assertStringStartsWith('color', $attr2->slug);
    }

    public function test_attribute_value_unique_per_attribute(): void
    {
        $attribute = Attribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'status' => 'active',
        ]);

        $attribute->values()->create([
            'value' => 'Red',
            'slug' => 'red',
        ]);

        $this->assertDatabaseHas('attribute_values', [
            'attribute_id' => $attribute->id,
            'value' => 'Red',
        ]);
    }

    public function test_admin_can_delete_attribute_value(): void
    {
        $attribute = Attribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'status' => 'active',
        ]);

        $value = $attribute->values()->create([
            'value' => 'Red',
            'slug' => 'red',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.attributes.values.destroy', $value));

        $response->assertRedirect();
        $this->assertDatabaseMissing('attribute_values', ['id' => $value->id]);
    }

    public function test_customer_cannot_access_attributes(): void
    {
        $customer = User::factory()->create();
        $customerRole = Role::firstOrCreate(['slug' => 'customer', 'name' => 'Customer']);
        $customer->roles()->attach($customerRole);

        $response = $this->actingAs($customer)
            ->get(route('admin.attributes.index'));

        $response->assertForbidden();
    }

    public function test_attribute_scopes(): void
    {
        Attribute::create(['name' => 'Active', 'slug' => 'active', 'type' => 'text', 'status' => 'active', 'is_filterable' => true]);
        Attribute::create(['name' => 'Inactive', 'slug' => 'inactive', 'type' => 'text', 'status' => 'inactive']);
        Attribute::create(['name' => 'Variant', 'slug' => 'variant', 'type' => 'text', 'status' => 'active', 'is_variant' => true]);

        $this->assertEquals(2, Attribute::active()->count());
        $this->assertEquals(1, Attribute::filterable()->count());
        $this->assertEquals(1, Attribute::variant()->count());
    }

    public function test_attribute_status_badge(): void
    {
        $active = Attribute::create(['name' => 'A', 'slug' => 'a', 'type' => 'text', 'status' => 'active']);
        $inactive = Attribute::create(['name' => 'B', 'slug' => 'b', 'type' => 'text', 'status' => 'inactive']);

        $this->assertEquals('bg-green-100 text-green-800', $active->status_badge);
        $this->assertEquals('bg-red-100 text-red-800', $inactive->status_badge);
    }

    public function test_attribute_full_name_with_bn(): void
    {
        $attr = Attribute::create(['name' => 'Color', 'name_bn' => 'রঙ', 'slug' => 'color', 'type' => 'select', 'status' => 'active']);
        $this->assertEquals('Color (রঙ)', $attr->full_name);
    }

    public function test_attribute_value_full_name(): void
    {
        $attribute = Attribute::create(['name' => 'Color', 'slug' => 'color', 'type' => 'select', 'status' => 'active']);
        $value = $attribute->values()->create(['value' => 'Red', 'value_bn' => 'লাল', 'slug' => 'red']);
        $this->assertEquals('Red (লাল)', $value->full_name);

        $value2 = $attribute->values()->create(['value' => 'Blue', 'slug' => 'blue']);
        $this->assertEquals('Blue', $value2->full_name);
    }

    public function test_attribute_index_filter_by_status(): void
    {
        Attribute::create(['name' => 'Active', 'slug' => 'active-attr', 'type' => 'text', 'status' => 'active']);
        Attribute::create(['name' => 'Inactive', 'slug' => 'inactive-attr', 'type' => 'text', 'status' => 'inactive']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.attributes.index', ['status' => 'active']));

        $response->assertOk();
    }

    public function test_attribute_index_search(): void
    {
        Attribute::create(['name' => 'Color', 'slug' => 'color', 'type' => 'select', 'status' => 'active']);
        Attribute::create(['name' => 'Size', 'slug' => 'size', 'type' => 'select', 'status' => 'active']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.attributes.index', ['search' => 'Color']));

        $response->assertOk();
    }

    public function test_attribute_values_with_update(): void
    {
        $attribute = Attribute::create([
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'select',
            'status' => 'active',
        ]);

        $value = $attribute->values()->create([
            'value' => 'Small',
            'slug' => 'small',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.attributes.update', $attribute), [
                'name' => 'Size',
                'type' => 'select',
                'status' => 'active',
                'values' => [
                    ['id' => $value->id, 'value' => 'Small'],
                    ['value' => 'Medium'],
                ],
            ]);

        $response->assertRedirect(route('admin.attributes.index'));
        $this->assertDatabaseHas('attribute_values', [
            'attribute_id' => $attribute->id,
            'value' => 'Small',
        ]);
        $this->assertDatabaseHas('attribute_values', [
            'attribute_id' => $attribute->id,
            'value' => 'Medium',
        ]);
    }
}
