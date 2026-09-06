<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $sellerUser;
    private Seller $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sellerUser = User::factory()->create();
        $this->seller = Seller::factory()->create([
            'user_id' => $this->sellerUser->id,
            'status' => 'approved',
        ]);
    }

    private function createOrderForSeller(array $overrides = []): Order
    {
        $order = Order::factory()->create([
            'seller_id' => $this->seller->id,
            'status' => 'pending',
            'payment_status' => 'paid',
            ...$overrides,
        ]);

        $product = Product::factory()->create(['seller_id' => $this->seller->id]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
        ]);

        return $order;
    }

    public function test_seller_can_view_orders_index(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.orders.index'));

        $response->assertOk();
        $response->assertViewIs('seller.orders.index');
    }

    public function test_pending_seller_redirected_from_orders(): void
    {
        $pendingUser = User::factory()->create();
        Seller::factory()->create([
            'user_id' => $pendingUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($pendingUser)
            ->get(route('seller.orders.index'));

        $response->assertRedirect(route('seller.dashboard'));
    }

    public function test_seller_can_view_order_detail(): void
    {
        $order = $this->createOrderForSeller();

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.orders.show', $order));

        $response->assertOk();
        $response->assertViewIs('seller.orders.show');
        $response->assertSee($order->order_number);
    }

    public function test_seller_cannot_view_other_seller_order(): void
    {
        $otherSeller = Seller::factory()->create([
            'user_id' => User::factory()->create()->id,
            'status' => 'approved',
        ]);
        $otherOrder = Order::factory()->create(['seller_id' => $otherSeller->id]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.orders.show', $otherOrder));

        $response->assertForbidden();
    }

    public function test_seller_can_update_status_from_pending_to_processing(): void
    {
        $order = $this->createOrderForSeller(['status' => 'pending']);

        $response = $this->actingAs($this->sellerUser)
            ->patch(route('seller.orders.update-status', $order), [
                'status' => 'processing',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'processing']);
    }

    public function test_seller_can_update_from_processing_to_shipped(): void
    {
        $order = $this->createOrderForSeller(['status' => 'processing']);

        $response = $this->actingAs($this->sellerUser)
            ->patch(route('seller.orders.update-status', $order), [
                'status' => 'shipped',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'shipped']);
    }

    public function test_seller_can_update_from_shipped_to_delivered(): void
    {
        $order = $this->createOrderForSeller(['status' => 'shipped']);

        $response = $this->actingAs($this->sellerUser)
            ->patch(route('seller.orders.update-status', $order), [
                'status' => 'delivered',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'delivered']);
    }

    public function test_seller_can_cancel_pending_order(): void
    {
        $order = $this->createOrderForSeller(['status' => 'pending']);

        $response = $this->actingAs($this->sellerUser)
            ->patch(route('seller.orders.update-status', $order), [
                'status' => 'cancelled',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
    }

    public function test_seller_cannot_skip_statuses(): void
    {
        $order = $this->createOrderForSeller(['status' => 'pending']);

        $response = $this->actingAs($this->sellerUser)
            ->patch(route('seller.orders.update-status', $order), [
                'status' => 'shipped',
            ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
    }

    public function test_seller_cannot_update_delivered_order(): void
    {
        $order = $this->createOrderForSeller(['status' => 'delivered']);

        $response = $this->actingAs($this->sellerUser)
            ->patch(route('seller.orders.update-status', $order), [
                'status' => 'processing',
            ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_status_change_creates_history(): void
    {
        $order = $this->createOrderForSeller(['status' => 'pending']);

        $this->actingAs($this->sellerUser)
            ->patch(route('seller.orders.update-status', $order), [
                'status' => 'processing',
                'note' => 'Starting preparation',
            ]);

        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'processing',
            'type' => 'status',
            'note' => 'Starting preparation',
        ]);
    }

    public function test_seller_can_add_note(): void
    {
        $order = $this->createOrderForSeller();

        $response = $this->actingAs($this->sellerUser)
            ->patch(route('seller.orders.note', $order), [
                'seller_notes' => 'Customer requested gift wrapping',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'seller_notes' => 'Customer requested gift wrapping',
        ]);
    }

    public function test_seller_can_update_tracking(): void
    {
        $order = $this->createOrderForSeller();

        $response = $this->actingAs($this->sellerUser)
            ->patch(route('seller.orders.tracking', $order), [
                'courier_name' => 'FedEx',
                'tracking_number' => 'FX123456789',
                'tracking_url' => 'https://fedex.com/track/FX123456789',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'courier_name' => 'FedEx',
            'tracking_number' => 'FX123456789',
        ]);
    }

    public function test_orders_index_shows_only_seller_orders(): void
    {
        $myOrder = $this->createOrderForSeller(['total' => 100]);

        $otherSeller = Seller::factory()->create([
            'user_id' => User::factory()->create()->id,
            'status' => 'approved',
        ]);
        Order::factory()->create([
            'seller_id' => $otherSeller->id,
            'total' => 999,
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.orders.index'));

        $response->assertOk();
        $response->assertSee('100');
        $response->assertDontSee('999');
    }

    public function test_orders_index_filter_by_status(): void
    {
        $this->createOrderForSeller(['status' => 'pending']);
        $this->createOrderForSeller(['status' => 'processing']);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.orders.index', ['status' => 'pending']));

        $response->assertOk();
    }

    public function test_orders_index_search(): void
    {
        $order = $this->createOrderForSeller();

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.orders.index', ['search' => $order->order_number]));

        $response->assertOk();
        $response->assertSee($order->order_number);
    }

    public function test_orders_index_filter_by_payment_status(): void
    {
        $this->createOrderForSeller(['payment_status' => 'paid']);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.orders.index', ['payment_status' => 'paid']));

        $response->assertOk();
    }

    public function test_unauthenticated_cannot_access_orders(): void
    {
        $response = $this->get(route('seller.orders.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_order_detail_shows_items(): void
    {
        $order = $this->createOrderForSeller();

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.orders.show', $order));

        $response->assertOk();
        $response->assertSee('Order Items');
        $response->assertSee('Shipping Address');
        $response->assertSee('Payment');
        $response->assertSee('Order Timeline');
    }

    public function test_status_validation_rejects_invalid_transitions(): void
    {
        $order = $this->createOrderForSeller(['status' => 'delivered']);

        $response = $this->actingAs($this->sellerUser)
            ->patch(route('seller.orders.update-status', $order), [
                'status' => 'pending',
            ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_bulk_update_processes_multiple_orders(): void
    {
        $order1 = $this->createOrderForSeller(['status' => 'pending']);
        $order2 = $this->createOrderForSeller(['status' => 'pending']);

        $response = $this->actingAs($this->sellerUser)
            ->post(route('seller.orders.bulk-update'), [
                'order_ids' => [$order1->id, $order2->id],
                'status' => 'processing',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order1->id, 'status' => 'processing']);
        $this->assertDatabaseHas('orders', ['id' => $order2->id, 'status' => 'processing']);
    }
}
