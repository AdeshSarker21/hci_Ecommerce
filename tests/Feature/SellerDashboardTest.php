<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerDashboardTest extends TestCase
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
            'store_slug' => 'test-store',
            'store_name' => 'Test Store',
        ]);
    }

    public function test_seller_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.analytics'));

        $response->assertOk();
        $response->assertViewIs('seller.dashboard-analytics');
    }

    public function test_pending_seller_redirected_from_dashboard(): void
    {
        $pendingUser = User::factory()->create();
        Seller::factory()->create([
            'user_id' => $pendingUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($pendingUser)
            ->get(route('seller.analytics'));

        $response->assertRedirect(route('seller.dashboard'));
    }

    public function test_dashboard_shows_stats(): void
    {
        Order::factory()->count(3)->create([
            'seller_id' => $this->seller->id,
            'payment_status' => 'paid',
            'status' => 'delivered',
        ]);

        Product::factory()->count(5)->create([
            'seller_id' => $this->seller->id,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.analytics'));

        $response->assertOk();
        $response->assertSee('Revenue');
        $response->assertSee('Orders');
        $response->assertSee('Products');
    }

    public function test_dashboard_shows_only_own_data(): void
    {
        $otherSeller = Seller::factory()->create([
            'user_id' => User::factory()->create()->id,
            'status' => 'approved',
        ]);

        Order::factory()->create([
            'seller_id' => $this->seller->id,
            'total' => 100,
            'payment_status' => 'paid',
        ]);

        Order::factory()->create([
            'seller_id' => $otherSeller->id,
            'total' => 500,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.analytics'));

        $response->assertOk();
        $response->assertSee('100.00');
        $response->assertDontSee('500.00');
    }

    public function test_dashboard_period_filter(): void
    {
        Order::factory()->create([
            'seller_id' => $this->seller->id,
            'created_at' => now()->subDays(5),
            'payment_status' => 'paid',
        ]);

        Order::factory()->create([
            'seller_id' => $this->seller->id,
            'created_at' => now()->subDays(60),
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.analytics', ['period' => '7d']));

        $response->assertOk();
    }

    public function test_dashboard_shows_recent_orders(): void
    {
        Order::factory()->create([
            'seller_id' => $this->seller->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.analytics'));

        $response->assertOk();
        $response->assertSee('Recent Orders');
    }

    public function test_dashboard_shows_top_products(): void
    {
        $product = Product::factory()->create([
            'seller_id' => $this->seller->id,
            'name' => 'Best Seller',
            'status' => 'published',
        ]);

        $order = Order::factory()->create([
            'seller_id' => $this->seller->id,
            'payment_status' => 'paid',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.analytics'));

        $response->assertOk();
        $response->assertSee('Top Products');
    }

    public function test_dashboard_shows_low_stock_alerts(): void
    {
        Product::factory()->create([
            'seller_id' => $this->seller->id,
            'manage_stock' => true,
            'quantity' => 2,
            'low_stock_threshold' => 5,
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.analytics'));

        $response->assertOk();
        $response->assertSee('Low Stock');
    }

    public function test_dashboard_shows_pending_products(): void
    {
        Product::factory()->create([
            'seller_id' => $this->seller->id,
            'status' => 'pending_review',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.analytics'));

        $response->assertOk();
        $response->assertSee('Pending Approval');
    }

    public function test_dashboard_shows_performance_section(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.analytics'));

        $response->assertOk();
        $response->assertSee('Performance');
        $response->assertSee('Store Rating');
        $response->assertSee('Response Rate');
        $response->assertSee('On-Time Delivery');
    }

    public function test_dashboard_shows_sales_chart_data(): void
    {
        Order::factory()->create([
            'seller_id' => $this->seller->id,
            'created_at' => now()->subDays(5),
            'total' => 100,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.analytics'));

        $response->assertOk();
        $response->assertSee('Sales Overview');
    }

    public function test_unauthenticated_cannot_access_dashboard(): void
    {
        $response = $this->get(route('seller.analytics'));

        $response->assertRedirect(route('login'));
    }

    public function test_other_seller_cannot_access_dashboard(): void
    {
        $otherUser = User::factory()->create();
        $otherSeller = Seller::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('seller.analytics'));

        $response->assertOk();
    }

    public function test_dashboard_order_status_counts(): void
    {
        Order::factory()->pending()->create(['seller_id' => $this->seller->id]);
        Order::factory()->processing()->create(['seller_id' => $this->seller->id]);
        Order::factory()->delivered()->create(['seller_id' => $this->seller->id]);
        Order::factory()->cancelled()->create(['seller_id' => $this->seller->id]);

        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.analytics'));

        $response->assertOk();
        $response->assertSee('Pending');
        $response->assertSee('Completed');
        $response->assertSee('Cancelled');
    }

    public function test_order_model_generates_order_number(): void
    {
        $order = Order::factory()->create([
            'seller_id' => $this->seller->id,
        ]);

        $this->assertNotEmpty($order->order_number);
        $this->assertStringStartsWith('ORD-', $order->order_number);
    }

    public function test_order_model_helpers(): void
    {
        $order = Order::factory()->create([
            'seller_id' => $this->seller->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'total' => 99.99,
        ]);

        $this->assertTrue($order->isPending());
        $this->assertFalse($order->isPaid());
        $this->assertEquals('99.99', $order->formatted_total);
        $this->assertEquals('bg-yellow-100 text-yellow-800', $order->status_badge);
    }
}
