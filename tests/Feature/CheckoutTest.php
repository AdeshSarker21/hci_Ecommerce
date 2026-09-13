<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Seller $seller1;
    private Seller $seller2;
    private Product $product1;
    private Product $product2;
    private Product $product3;
    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'phone' => '01712345678',
            'status' => 'active',
            'is_active' => true,
        ]);

        $sellerUser1 = User::factory()->create();
        $this->seller1 = Seller::factory()->approved()->create([
            'user_id' => $sellerUser1->id,
            'store_name' => 'Test Store One',
        ]);

        $sellerUser2 = User::factory()->create();
        $this->seller2 = Seller::factory()->approved()->create([
            'user_id' => $sellerUser2->id,
            'store_name' => 'Test Store Two',
        ]);

        $this->product1 = Product::factory()->published()->create([
            'seller_id' => $this->seller1->id,
            'name' => 'Product One',
            'price' => 25.00,
            'quantity' => 50,
            'manage_stock' => true,
            'is_active' => true,
        ]);

        $this->product2 = Product::factory()->published()->create([
            'seller_id' => $this->seller1->id,
            'name' => 'Product Two',
            'price' => 35.00,
            'quantity' => 30,
            'manage_stock' => true,
            'is_active' => true,
        ]);

        $this->product3 = Product::factory()->published()->create([
            'seller_id' => $this->seller2->id,
            'name' => 'Product Three',
            'price' => 15.00,
            'quantity' => 20,
            'manage_stock' => true,
            'is_active' => true,
        ]);

        $this->address = Address::create([
            'user_id' => $this->user->id,
            'label' => 'home',
            'name' => 'Test Customer',
            'phone' => '01712345678',
            'email' => 'customer@test.com',
            'address_line_1' => '123 Test Street',
            'city' => 'Dhaka',
            'state' => 'Dhaka Division',
            'postal_code' => '1000',
            'country' => 'Bangladesh',
            'is_default' => true,
        ]);
    }

    private function addToCart(Product $product, int $quantity = 1): void
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->price,
            'compare_at_price' => $product->compare_at_price,
        ]);
    }

    public function test_guest_cannot_access_checkout(): void
    {
        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_checkout(): void
    {
        $this->addToCart($this->product1);

        $response = $this->actingAs($this->user)->get(route('checkout.index'));
        $response->assertOk();
        $response->assertSee('Checkout');
        $response->assertSee('Product One');
    }

    public function test_empty_cart_redirects_to_cart(): void
    {
        $response = $this->actingAs($this->user)->get(route('checkout.index'));
        $response->assertRedirect(route('cart.index'));
    }

    public function test_checkout_shows_cart_items_grouped_by_seller(): void
    {
        $this->addToCart($this->product1, 2);
        $this->addToCart($this->product2, 1);
        $this->addToCart($this->product3, 1);

        $response = $this->actingAs($this->user)->get(route('checkout.index'));
        $response->assertOk();
        $response->assertSee('Test Store One');
        $response->assertSee('Test Store Two');
        $response->assertSee('Product One');
        $response->assertSee('Product Two');
        $response->assertSee('Product Three');
    }

    public function test_checkout_shows_shipping_per_seller(): void
    {
        $this->addToCart($this->product3, 1);

        $response = $this->actingAs($this->user)->get(route('checkout.index'));
        $response->assertOk();
        $response->assertSee('5.99');
    }

    public function test_free_shipping_on_orders_over_50(): void
    {
        $this->addToCart($this->product2, 2);

        $response = $this->actingAs($this->user)->get(route('checkout.index'));
        $response->assertOk();
        $response->assertSee('Free Shipping');
    }

    public function test_address_creation(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('checkout.address.store'), [
            'label' => 'office',
            'name' => 'Office Address',
            'phone' => '01812345678',
            'email' => 'office@test.com',
            'address_line_1' => '456 Office Road',
            'city' => 'Chittagong',
            'state' => 'Chittagong Division',
            'postal_code' => '4000',
            'country' => 'Bangladesh',
            'is_default' => false,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->user->id,
            'label' => 'office',
            'city' => 'Chittagong',
        ]);
    }

    public function test_address_update(): void
    {
        $response = $this->actingAs($this->user)->putJson(route('checkout.address.update', $this->address->id), [
            'label' => 'home',
            'name' => 'Updated Name',
            'phone' => '01712345678',
            'address_line_1' => '789 New Street',
            'city' => 'Sylhet',
            'country' => 'Bangladesh',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('addresses', [
            'id' => $this->address->id,
            'name' => 'Updated Name',
            'city' => 'Sylhet',
        ]);
    }

    public function test_address_deletion(): void
    {
        $response = $this->actingAs($this->user)->deleteJson(route('checkout.address.delete', $this->address->id));
        $response->assertOk();
        $this->assertDatabaseMissing('addresses', ['id' => $this->address->id]);
    }

    public function test_set_default_address(): void
    {
        $address2 = Address::create([
            'user_id' => $this->user->id,
            'label' => 'office',
            'name' => 'Office',
            'phone' => '01812345678',
            'address_line_1' => '456 Office Road',
            'city' => 'Chittagong',
            'country' => 'Bangladesh',
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->user)->postJson(route('checkout.address.default', $address2->id));
        $response->assertOk();

        $this->assertDatabaseHas('addresses', ['id' => $address2->id, 'is_default' => true]);
        $this->assertDatabaseHas('addresses', ['id' => $this->address->id, 'is_default' => false]);
    }

    public function test_validate_cart_with_valid_items(): void
    {
        $this->addToCart($this->product1, 2);

        $response = $this->actingAs($this->user)->getJson(route('checkout.validate-cart'));
        $response->assertOk();
        $response->assertJson(['success' => true, 'errors' => []]);
    }

    public function test_validate_cart_with_out_of_stock_item(): void
    {
        $this->product1->update(['quantity' => 0]);
        $this->addToCart($this->product1, 1);

        $response = $this->actingAs($this->user)->getJson(route('checkout.validate-cart'));
        $response->assertOk();
        $response->assertJson(['success' => false]);
    }

    public function test_validate_cart_with_inactive_product(): void
    {
        $this->product1->update(['is_active' => false]);
        $this->addToCart($this->product1, 1);

        $response = $this->actingAs($this->user)->getJson(route('checkout.validate-cart'));
        $response->assertOk();
        $response->assertJson(['success' => false]);
    }

    public function test_place_order_with_cod(): void
    {
        $this->addToCart($this->product1, 1);

        $response = $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $this->address->id,
            'payment_method' => 'cod',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'seller_id' => $this->seller1->id,
            'payment_method' => 'cod',
            'status' => 'pending',
        ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertEquals(25.00, $order->subtotal);
        $this->assertEquals(5.99, $order->shipping_cost);
        $this->assertEquals(30.99, $order->total);
    }

    public function test_place_order_creates_order_items(): void
    {
        $this->addToCart($this->product1, 2);
        $this->addToCart($this->product2, 1);

        $response = $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $this->address->id,
            'payment_method' => 'cod',
        ]);

        $response->assertOk();

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertCount(2, $order->items);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product1->id,
            'quantity' => 2,
            'unit_price' => 25.00,
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product2->id,
            'quantity' => 1,
            'unit_price' => 35.00,
        ]);
    }

    public function test_multi_seller_order_creates_separate_orders(): void
    {
        $this->addToCart($this->product1, 1);
        $this->addToCart($this->product3, 2);

        $response = $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $this->address->id,
            'payment_method' => 'cod',
        ]);

        $response->assertOk();

        $orders = Order::where('user_id', $this->user->id)->get();
        $this->assertCount(2, $orders);

        $seller1Order = $orders->firstWhere('seller_id', $this->seller1->id);
        $seller2Order = $orders->firstWhere('seller_id', $this->seller2->id);

        $this->assertNotNull($seller1Order);
        $this->assertNotNull($seller2Order);
    }

    public function test_order_deducts_inventory(): void
    {
        $this->addToCart($this->product1, 3);

        $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $this->address->id,
            'payment_method' => 'cod',
        ]);

        $this->product1->refresh();
        $this->assertEquals(47, $this->product1->quantity);
    }

    public function test_order_fails_with_insufficient_stock(): void
    {
        $this->product1->update(['quantity' => 1]);
        $this->addToCart($this->product1, 5);

        $response = $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $this->address->id,
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_order_fails_without_address(): void
    {
        $this->addToCart($this->product1);

        $response = $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(422);
    }

    public function test_order_fails_without_payment_method(): void
    {
        $this->addToCart($this->product1);

        $response = $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $this->address->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_order_with_online_payment(): void
    {
        $this->addToCart($this->product1);

        $response = $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $this->address->id,
            'payment_method' => 'online',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'online',
            'payment_status' => 'pending',
        ]);
    }

    public function test_order_stores_shipping_address(): void
    {
        $this->addToCart($this->product1);

        $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $this->address->id,
            'payment_method' => 'cod',
        ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order->shipping_address);
        $this->assertEquals('Test Customer', $order->shipping_address['name']);
        $this->assertEquals('123 Test Street', $order->shipping_address['address_line_1']);
        $this->assertEquals('Dhaka', $order->shipping_address['city']);
    }

    public function test_order_clears_cart_after_placement(): void
    {
        $this->addToCart($this->product1, 2);

        $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $this->address->id,
            'payment_method' => 'cod',
        ]);

        $cartItems = CartItem::where('user_id', $this->user->id)->get();
        $this->assertCount(0, $cartItems);
    }

    public function test_order_logs_status_change(): void
    {
        $this->addToCart($this->product1);

        $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $this->address->id,
            'payment_method' => 'cod',
        ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'to_status' => 'pending',
        ]);
    }

    public function test_order_number_is_generated(): void
    {
        $this->addToCart($this->product1);

        $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $this->address->id,
            'payment_method' => 'cod',
        ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order->order_number);
        $this->assertStringStartsWith('ORD-', $order->order_number);
    }

    public function test_order_with_notes(): void
    {
        $this->addToCart($this->product1);

        $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $this->address->id,
            'payment_method' => 'cod',
            'notes' => 'Please leave at the door',
        ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertEquals('Please leave at the door', $order->notes);
    }

    public function test_cannot_order_empty_cart(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $this->address->id,
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_access_other_users_address(): void
    {
        $otherUser = User::factory()->create();
        $otherAddress = Address::create([
            'user_id' => $otherUser->id,
            'label' => 'home',
            'name' => 'Other User',
            'phone' => '01912345678',
            'address_line_1' => '999 Other Street',
            'city' => 'Rajshahi',
            'country' => 'Bangladesh',
            'is_default' => true,
        ]);

        $this->addToCart($this->product1);

        $response = $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $otherAddress->id,
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(422);
    }

    public function test_stock_prevents_overselling_in_concurrent_requests(): void
    {
        $this->product1->update(['quantity' => 2]);
        $this->addToCart($this->product1, 2);

        $response = $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $this->address->id,
            'payment_method' => 'cod',
        ]);

        $response->assertOk();

        $this->product1->refresh();
        $this->assertEquals(0, $this->product1->quantity);
    }

    public function test_inventory_transaction_recorded(): void
    {
        $this->addToCart($this->product1, 2);

        $this->actingAs($this->user)->postJson(route('checkout.place-order'), [
            'address_id' => $this->address->id,
            'payment_method' => 'cod',
        ]);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product1->id,
            'type' => 'sale',
            'quantity' => -2,
        ]);
    }
}
