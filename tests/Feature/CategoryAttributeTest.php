<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Role;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryAttributeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $sellerUser;
    private Seller $seller;
    private Category $category;
    private Attribute $attribute;

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

        $this->category = Category::create([
            'name' => 'Electronics',
            'status' => 'active',
        ]);

        $this->attribute = Attribute::create([
            'name' => 'Color',
            'type' => 'select',
            'status' => 'active',
        ]);

        AttributeValue::create([
            'attribute_id' => $this->attribute->id,
            'value' => 'Red',
            'slug' => 'red',
        ]);
        AttributeValue::create([
            'attribute_id' => $this->attribute->id,
            'value' => 'Blue',
            'slug' => 'blue',
        ]);
    }

    // --- Admin Category Attribute Management ---

    public function test_admin_can_view_category_attributes_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.category-attributes.index'));

        $response->assertOk();
        $response->assertViewIs('admin.category-attributes.index');
    }

    public function test_admin_can_edit_category_attributes(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.category-attributes.edit', $this->category));

        $response->assertOk();
        $response->assertViewIs('admin.category-attributes.edit');
    }

    public function test_admin_can_assign_attribute_to_category(): void
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.category-attributes.update', $this->category), [
                'attributes' => [$this->attribute->id],
                'sort_order' => [$this->attribute->id => 1],
                'is_required' => [$this->attribute->id => 1],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('category_attributes', [
            'category_id' => $this->category->id,
            'attribute_id' => $this->attribute->id,
            'sort_order' => 1,
            'is_required' => true,
        ]);
    }

    public function test_admin_can_remove_attribute_from_category(): void
    {
        $this->category->attributes()->attach($this->attribute, [
            'sort_order' => 0,
            'is_required' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.category-attributes.update', $this->category), [
                'attributes' => [],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseMissing('category_attributes', [
            'category_id' => $this->category->id,
            'attribute_id' => $this->attribute->id,
        ]);
    }

    public function test_admin_get_category_attributes_json(): void
    {
        $this->category->attributes()->attach($this->attribute, [
            'sort_order' => 0,
            'is_required' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.category-attributes.get-attributes', $this->category));

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => 'Color',
            'type' => 'select',
            'is_required' => 1,
        ]);
    }

    public function test_duplicate_attribute_assignment_is_prevented(): void
    {
        $this->category->attributes()->attach($this->attribute, [
            'sort_order' => 0,
            'is_required' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.category-attributes.update', $this->category), [
                'attributes' => [$this->attribute->id],
                'sort_order' => [$this->attribute->id => 2],
                'is_required' => [$this->attribute->id => 1],
            ]);

        $response->assertRedirect();

        $count = CategoryAttribute::where('category_id', $this->category->id)
            ->where('attribute_id', $this->attribute->id)
            ->count();
        $this->assertEquals(1, $count);
    }

    // --- Seller Product Attribute Values ---

    public function test_seller_can_get_category_attributes(): void
    {
        $this->category->attributes()->attach($this->attribute, [
            'sort_order' => 0,
            'is_required' => true,
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.category-attributes.get', $this->category));

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => 'Color',
            'type' => 'select',
        ]);
    }

    public function test_seller_can_create_product_with_attribute_values(): void
    {
        $this->category->attributes()->attach($this->attribute, [
            'sort_order' => 0,
            'is_required' => false,
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.store'), [
                'name' => 'Test Product',
                'type' => 'physical',
                'price' => 29.99,
                'category_id' => $this->category->id,
                'spec' => [
                    $this->attribute->id => 'Red',
                ],
            ]);

        $response->assertRedirect();

        $product = Product::where('name', 'Test Product')->first();
        $this->assertNotNull($product);

        $this->assertDatabaseHas('product_attribute_values', [
            'product_id' => $product->id,
            'attribute_id' => $this->attribute->id,
            'value' => 'Red',
        ]);
    }

    public function test_seller_can_update_product_attribute_values(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Existing Product',
            'price' => 19.99,
            'category_id' => $this->category->id,
            'status' => 'draft',
        ]);

        ProductAttributeValue::create([
            'product_id' => $product->id,
            'attribute_id' => $this->attribute->id,
            'value' => 'Red',
        ]);

        $this->category->attributes()->attach($this->attribute, [
            'sort_order' => 0,
            'is_required' => false,
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->put(route('seller.products.update', $product), [
                'name' => 'Existing Product',
                'price' => 19.99,
                'spec' => [
                    $this->attribute->id => 'Blue',
                ],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('product_attribute_values', [
            'product_id' => $product->id,
            'attribute_id' => $this->attribute->id,
            'value' => 'Blue',
        ]);

        $this->assertDatabaseMissing('product_attribute_values', [
            'product_id' => $product->id,
            'attribute_id' => $this->attribute->id,
            'value' => 'Red',
        ]);
    }

    public function test_empty_spec_values_are_not_saved(): void
    {
        $this->category->attributes()->attach($this->attribute, [
            'sort_order' => 0,
            'is_required' => false,
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.products.store'), [
                'name' => 'No Specs Product',
                'type' => 'physical',
                'price' => 15.00,
                'category_id' => $this->category->id,
                'spec' => [
                    $this->attribute->id => '',
                ],
            ]);

        $response->assertRedirect();

        $product = Product::where('name', 'No Specs Product')->first();
        $this->assertNotNull($product);
        $this->assertEquals(0, $product->attributeValues()->count());
    }

    // --- Model Relationships ---

    public function test_category_has_attributes_relationship(): void
    {
        $this->category->attributes()->attach($this->attribute, [
            'sort_order' => 0,
            'is_required' => false,
        ]);

        $this->assertCount(1, $this->category->attributes);
        $this->assertEquals($this->attribute->id, $this->category->attributes->first()->id);
    }

    public function test_attribute_has_categories_relationship(): void
    {
        $this->category->attributes()->attach($this->attribute, [
            'sort_order' => 0,
            'is_required' => false,
        ]);

        $this->assertCount(1, $this->attribute->categories);
        $this->assertEquals($this->category->id, $this->attribute->categories->first()->id);
    }

    public function test_product_has_attribute_values_relationship(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Test',
            'price' => 10.00,
            'status' => 'draft',
        ]);

        ProductAttributeValue::create([
            'product_id' => $product->id,
            'attribute_id' => $this->attribute->id,
            'value' => 'Red',
        ]);

        $this->assertCount(1, $product->attributeValues);
        $this->assertEquals('Red', $product->attributeValues->first()->value);
    }

    public function test_category_attribute_pivot_has_correct_data(): void
    {
        $this->category->attributes()->attach($this->attribute, [
            'sort_order' => 5,
            'is_required' => true,
        ]);

        $pivot = $this->category->attributes->first()->pivot;
        $this->assertEquals(5, $pivot->sort_order);
        $this->assertTrue((bool) $pivot->is_required);
    }

    public function test_product_attribute_value_display_value(): void
    {
        $attrValue = AttributeValue::where('attribute_id', $this->attribute->id)->first();

        $pav = ProductAttributeValue::create([
            'product_id' => Product::create([
                'seller_id' => $this->seller->id,
                'name' => 'Test',
                'price' => 10.00,
                'status' => 'draft',
            ])->id,
            'attribute_id' => $this->attribute->id,
            'attribute_value_id' => $attrValue->id,
            'value' => 'Red',
        ]);

        $this->assertEquals($attrValue->full_name, $pav->display_value);
    }
}
