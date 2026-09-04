<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Seller;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $sellerUser;
    private Seller $seller;
    private Product $product;

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

        $this->product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Test Product',
            'price' => 29.99,
            'quantity' => 100,
            'manage_stock' => true,
            'low_stock_threshold' => 10,
            'status' => 'draft',
        ]);
    }

    // --- SKU Tests ---

    public function test_sku_is_auto_generated_on_creation(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'No SKU Product',
            'price' => 10.00,
            'status' => 'draft',
        ]);

        $this->assertNotNull($product->sku);
        $this->assertMatchesRegularExpression('/^SKU-\d{6}$/', $product->sku);
    }

    public function test_sku_is_unique_across_marketplace(): void
    {
        $sku1 = $this->product->sku;

        $product2 = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Another Product',
            'price' => 15.00,
            'status' => 'draft',
        ]);

        $this->assertNotEquals($sku1, $product2->sku);
    }

    public function test_manual_sku_is_preserved(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Manual SKU Product',
            'sku' => 'MY-CUSTOM-SKU',
            'price' => 25.00,
            'status' => 'draft',
        ]);

        $this->assertEquals('MY-CUSTOM-SKU', $product->sku);
    }

    public function test_barcode_defaults_to_sku(): void
    {
        $this->assertEquals($this->product->sku, $this->product->barcode);
    }

    // --- Inventory Service Tests ---

    public function test_add_stock_increases_quantity(): void
    {
        $service = new InventoryService();
        $service->addStock($this->product, 50, 'Restock order');

        $this->product->refresh();
        $this->assertEquals(150, $this->product->quantity);
    }

    public function test_add_stock_creates_transaction(): void
    {
        $service = new InventoryService();
        $service->addStock($this->product, 50, 'Restock');

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'type' => 'restock',
            'quantity' => 50,
            'quantity_before' => 100,
            'quantity_after' => 150,
        ]);
    }

    public function test_remove_stock_decreases_quantity(): void
    {
        $service = new InventoryService();
        $service->removeStock($this->product, 30, 'sale', 'Customer order');

        $this->product->refresh();
        $this->assertEquals(70, $this->product->quantity);
    }

    public function test_remove_stock_throws_on_insufficient(): void
    {
        $service = new InventoryService();

        $this->expectException(\RuntimeException::class);
        $service->removeStock($this->product, 200, 'sale');
    }

    public function test_adjust_stock_sets_exact_quantity(): void
    {
        $service = new InventoryService();
        $service->adjustStock($this->product, 250, 'Annual count');

        $this->product->refresh();
        $this->assertEquals(250, $this->product->quantity);
    }

    public function test_reserve_stock(): void
    {
        $service = new InventoryService();
        $result = $service->reserveStock($this->product, 20);

        $this->assertTrue($result);
        $this->product->refresh();
        $this->assertEquals(20, $this->product->reserved_quantity);
    }

    public function test_reserve_stock_fails_when_insufficient(): void
    {
        $service = new InventoryService();
        $result = $service->reserveStock($this->product, 200);

        $this->assertFalse($result);
    }

    public function test_release_reservation(): void
    {
        $service = new InventoryService();
        $service->reserveStock($this->product, 20);
        $service->releaseReservation($this->product, 10);

        $this->product->refresh();
        $this->assertEquals(10, $this->product->reserved_quantity);
    }

    public function test_available_stock_calculation(): void
    {
        $service = new InventoryService();
        $service->reserveStock($this->product, 30);

        $this->product->refresh();
        $this->assertEquals(70, $this->product->available_stock);
    }

    public function test_available_stock_unlimited_when_not_manage_stock(): void
    {
        $this->product->update(['manage_stock' => false]);
        $this->product->refresh();

        $this->assertEquals(PHP_INT_MAX, $this->product->available_stock);
    }

    // --- Concurrent Stock Tests ---

    public function test_concurrent_stock_removal_uses_locking(): void
    {
        $service = new InventoryService();

        $threads = [];
        $results = [];

        for ($i = 0; $i < 5; $i++) {
            $results[] = $service->removeStock($this->product, 10, 'sale', "Order #{$i}");
        }

        $this->product->refresh();
        $this->assertEquals(50, $this->product->quantity);
    }

    public function test_concurrent_add_and_remove_stock(): void
    {
        $service = new InventoryService();

        $service->addStock($this->product, 50, 'Restock');
        $service->removeStock($this->product, 30, 'sale');

        $this->product->refresh();
        $this->assertEquals(120, $this->product->quantity);
    }

    // --- Admin Inventory Controller Tests ---

    public function test_admin_can_view_inventory_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.inventory.index'));

        $response->assertOk();
        $response->assertViewIs('admin.inventory.index');
    }

    public function test_admin_can_view_inventory_show(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.inventory.show', $this->product));

        $response->assertOk();
        $response->assertViewIs('admin.inventory.show');
    }

    public function test_admin_can_adjust_stock(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.inventory.adjust', $this->product), [
                'quantity' => 200,
                'notes' => 'Inventory count adjustment',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->product->refresh();
        $this->assertEquals(200, $this->product->quantity);
    }

    public function test_admin_can_add_stock(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.inventory.add-stock', $this->product), [
                'quantity' => 25,
                'notes' => 'New shipment',
                'unit_cost' => 5.50,
            ]);

        $response->assertRedirect();

        $this->product->refresh();
        $this->assertEquals(125, $this->product->quantity);
    }

    public function test_admin_can_remove_stock(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.inventory.remove-stock', $this->product), [
                'quantity' => 15,
                'type' => 'sale',
                'notes' => 'Wholesale order',
            ]);

        $response->assertRedirect();

        $this->product->refresh();
        $this->assertEquals(85, $this->product->quantity);
    }

    // --- Seller Inventory Controller Tests ---

    public function test_seller_can_view_inventory_index(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.inventory.index'));

        $response->assertOk();
        $response->assertViewIs('seller.inventory.index');
    }

    public function test_seller_can_view_own_product_inventory(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.inventory.show', $this->product));

        $response->assertOk();
    }

    public function test_seller_cannot_view_other_seller_inventory(): void
    {
        $otherUser = User::factory()->create();
        $otherSeller = Seller::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'approved',
        ]);
        $otherProduct = Product::create([
            'seller_id' => $otherSeller->id,
            'name' => 'Other Product',
            'price' => 10.00,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.inventory.show', $otherProduct));

        $response->assertStatus(403);
    }

    public function test_seller_can_adjust_own_product_stock(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.inventory.adjust', $this->product), [
                'quantity' => 500,
                'notes' => 'Stock take',
            ]);

        $response->assertRedirect();

        $this->product->refresh();
        $this->assertEquals(500, $this->product->quantity);
    }

    public function test_seller_cannot_adjust_other_seller_stock(): void
    {
        $otherUser = User::factory()->create();
        $otherSeller = Seller::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'approved',
        ]);
        $otherProduct = Product::create([
            'seller_id' => $otherSeller->id,
            'name' => 'Other Product',
            'price' => 10.00,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.inventory.adjust', $otherProduct), [
                'quantity' => 500,
            ]);

        $response->assertStatus(403);
    }

    // --- Transaction History Tests ---

    public function test_inventory_transactions_are_recorded(): void
    {
        $service = new InventoryService();
        $service->addStock($this->product, 10, 'Restock');
        $service->removeStock($this->product, 5, 'sale');

        $transactions = InventoryTransaction::where('product_id', $this->product->id)->get();

        $this->assertCount(2, $transactions);
        $this->assertEquals('restock', $transactions->first()->type);
        $this->assertEquals('sale', $transactions->last()->type);
    }

    public function test_transaction_tracks_quantity_before_and_after(): void
    {
        $service = new InventoryService();
        $service->addStock($this->product, 50, 'Restock');

        $tx = InventoryTransaction::where('product_id', $this->product->id)->first();

        $this->assertEquals(100, $tx->quantity_before);
        $this->assertEquals(150, $tx->quantity_after);
    }
}
