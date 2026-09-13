<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Seller $seller;
    protected Product $product;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_active' => true]);
        $sellerUser = User::factory()->create(['is_active' => true]);
        $this->seller = Seller::factory()->create(['status' => 'approved', 'user_id' => $sellerUser->id]);

        $this->product = Product::create([
            'seller_id' => $this->seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product-' . uniqid(),
            'price' => 29.99,
            'sku' => 'SKU-TEST-' . strtoupper(uniqid()),
            'quantity' => 50,
            'manage_stock' => true,
            'low_stock_threshold' => 5,
            'is_active' => true,
            'status' => 'published',
        ]);

        $this->order = Order::create([
            'order_number' => 'ORD-20260909-TEST01',
            'seller_id' => $this->seller->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'subtotal' => 29.99,
            'tax' => 0,
            'shipping_cost' => 5.99,
            'discount' => 0,
            'total' => 35.98,
            'currency' => 'USD',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_address' => [
                'name' => 'Test User',
                'phone' => '01700000000',
                'address_line_1' => '123 Test Street',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'postal_code' => '1212',
                'country' => 'BD',
            ],
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku' => $this->product->sku,
            'quantity' => 1,
            'unit_price' => 29.99,
            'total' => 29.99,
        ]);
    }

    public function test_guest_cannot_access_order_history(): void
    {
        $this->get(route('account.orders.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_order_history(): void
    {
        $this->actingAs($this->user)
            ->get(route('account.orders.index'))
            ->assertOk()
            ->assertSee('My Orders')
            ->assertSee($this->order->order_number);
    }

    public function test_order_history_shows_stats(): void
    {
        $this->actingAs($this->user)
            ->get(route('account.orders.index'))
            ->assertOk()
            ->assertSee('Total')
            ->assertSee('Pending');
    }

    public function test_user_can_view_own_order_details(): void
    {
        $this->actingAs($this->user)
            ->get(route('account.orders.show', $this->order))
            ->assertOk()
            ->assertSee('Order Details')
            ->assertSee($this->order->order_number)
            ->assertSee($this->product->name)
            ->assertSee('29.99')
            ->assertSee('Pending');
    }

    public function test_user_cannot_view_other_users_order(): void
    {
        $otherUser = User::factory()->create(['is_active' => true]);

        $this->actingAs($otherUser)
            ->get(route('account.orders.show', $this->order))
            ->assertForbidden();
    }

    public function test_user_can_view_order_confirmation(): void
    {
        $this->actingAs($this->user)
            ->get(route('account.orders.confirmation', $this->order))
            ->assertOk()
            ->assertSee('Thank You for Your Order')
            ->assertSee($this->order->order_number)
            ->assertSee('View Order Details')
            ->assertSee('Continue Shopping');
    }

    public function test_user_cannot_view_other_users_order_confirmation(): void
    {
        $otherUser = User::factory()->create(['is_active' => true]);

        $this->actingAs($otherUser)
            ->get(route('account.orders.confirmation', $this->order))
            ->assertForbidden();
    }

    public function test_order_details_shows_timeline(): void
    {
        $this->actingAs($this->user)
            ->get(route('account.orders.show', $this->order))
            ->assertOk()
            ->assertSee('Order Status')
            ->assertSee('Order Placed')
            ->assertSee('Shipped')
            ->assertSee('Delivered');
    }

    public function test_order_details_shows_shipping_address(): void
    {
        $this->actingAs($this->user)
            ->get(route('account.orders.show', $this->order))
            ->assertOk()
            ->assertSee('Shipping Address')
            ->assertSee('Test User')
            ->assertSee('123 Test Street')
            ->assertSee('Dhaka');
    }

    public function test_order_details_shows_payment_info(): void
    {
        $this->actingAs($this->user)
            ->get(route('account.orders.show', $this->order))
            ->assertOk()
            ->assertSee('Payment')
            ->assertSee('Cash on Delivery')
            ->assertSee('Pending');
    }

    public function test_order_details_shows_seller_info(): void
    {
        $this->actingAs($this->user)
            ->get(route('account.orders.show', $this->order))
            ->assertOk()
            ->assertSee('Sold by')
            ->assertSee($this->seller->store_name);
    }

    public function test_order_details_shows_items_count(): void
    {
        $this->actingAs($this->user)
            ->get(route('account.orders.show', $this->order))
            ->assertOk()
            ->assertSee('Order Items');
    }

    public function test_order_history_empty_state(): void
    {
        $emptyUser = User::factory()->create(['is_active' => true]);

        $this->actingAs($emptyUser)
            ->get(route('account.orders.index'))
            ->assertOk()
            ->assertSee('No orders yet')
            ->assertSee('Browse Products');
    }

    public function test_order_confirmation_shows_order_summary(): void
    {
        $this->actingAs($this->user)
            ->get(route('account.orders.confirmation', $this->order))
            ->assertOk()
            ->assertSee('Subtotal')
            ->assertSee('29.99')
            ->assertSee('Shipping')
            ->assertSee('5.99')
            ->assertSee('Total')
            ->assertSee('35.98');
    }

    public function test_order_with_tracking_shows_shipment_info(): void
    {
        $this->order->update([
            'tracking_number' => 'TRK123456789',
            'courier_name' => 'Pathao Courier',
            'tracking_url' => 'https://example.com/track/TRK123456789',
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->get(route('account.orders.show', $this->order))
            ->assertOk()
            ->assertSee('Shipment Information')
            ->assertSee('Pathao Courier')
            ->assertSee('TRK123456789')
            ->assertSee('Track Shipment');
    }

    public function test_order_without_tracking_hides_shipment_section(): void
    {
        $this->actingAs($this->user)
            ->get(route('account.orders.show', $this->order))
            ->assertOk()
            ->assertDontSee('Shipment Information');
    }

    public function test_back_to_orders_link_present(): void
    {
        $this->actingAs($this->user)
            ->get(route('account.orders.show', $this->order))
            ->assertOk()
            ->assertSee('Back to Orders');
    }
}
