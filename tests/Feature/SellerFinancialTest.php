<?php

namespace Tests\Feature;

use App\Models\CommissionRecord;
use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\CommissionService;
use App\Services\SettlementService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerFinancialTest extends TestCase
{
    use RefreshDatabase;

    private User $sellerUser;
    private Seller $seller;
    private CommissionService $commissionService;
    private WalletService $walletService;
    private SettlementService $settlementService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sellerUser = User::factory()->create();
        $this->seller = Seller::factory()->create([
            'user_id' => $this->sellerUser->id,
            'status' => 'approved',
        ]);

        $this->walletService = app(WalletService::class);
        $this->commissionService = app(CommissionService::class);
        $this->settlementService = app(SettlementService::class);
    }

    private function createPaidOrder(array $overrides = []): Order
    {
        $order = Order::factory()->create([
            'seller_id' => $this->seller->id,
            'payment_status' => 'paid',
            'status' => 'delivered',
            'total' => 100.00,
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

    // --- Commission Tests ---

    public function test_commission_calculated_with_default_rate(): void
    {
        $order = $this->createPaidOrder(['total' => 200.00]);

        $record = $this->commissionService->calculateForOrder($order);

        $this->assertNotNull($record);
        $this->assertEquals(200.00, (float) $record->order_amount);
        $this->assertEquals(20.00, (float) $record->commission_amount); // 10% default
        $this->assertEquals(180.00, (float) $record->seller_earnings);
        $this->assertEquals('pending', $record->status);
    }

    public function test_commission_respects_percentage_rule(): void
    {
        CommissionRule::create([
            'name' => 'High Commission',
            'type' => 'percentage',
            'value' => 25,
            'applies_to' => 'global',
            'is_active' => true,
            'priority' => 10,
        ]);

        $order = $this->createPaidOrder(['total' => 100.00]);
        $record = $this->commissionService->calculateForOrder($order);

        $this->assertEquals(25.00, (float) $record->commission_amount);
        $this->assertEquals(75.00, (float) $record->seller_earnings);
    }

    public function test_commission_respects_fixed_rule(): void
    {
        CommissionRule::create([
            'name' => 'Fixed Fee',
            'type' => 'fixed',
            'value' => 5.00,
            'applies_to' => 'global',
            'is_active' => true,
        ]);

        $order = $this->createPaidOrder(['total' => 100.00]);
        $record = $this->commissionService->calculateForOrder($order);

        $this->assertEquals(5.00, (float) $record->commission_amount);
        $this->assertEquals(95.00, (float) $record->seller_earnings);
    }

    public function test_commission_not_duplicated_for_same_order(): void
    {
        $order = $this->createPaidOrder();

        $record1 = $this->commissionService->calculateForOrder($order);
        $record2 = $this->commissionService->calculateForOrder($order);

        $this->assertNotNull($record1);
        $this->assertEquals($record1->id, $record2->id);
        $this->assertEquals(1, CommissionRecord::where('order_id', $order->id)->count());
    }

    public function test_commission_not_created_for_unpaid_order(): void
    {
        $order = $this->createPaidOrder(['payment_status' => 'pending']);

        $record = $this->commissionService->calculateForOrder($order);

        $this->assertNull($record);
        $this->assertEquals(0, CommissionRecord::where('order_id', $order->id)->count());
    }

    public function test_commission_cancelled_order_reverses_earnings(): void
    {
        $order = $this->createPaidOrder(['total' => 100.00]);

        $record = $this->commissionService->calculateForOrder($order);
        $this->assertEquals(90.00, (float) $record->seller_earnings);

        $this->commissionService->cancelForOrder($order);

        $record->refresh();
        $this->assertEquals('cancelled', $record->status);
    }

    public function test_seller_specific_rule_overrides_global(): void
    {
        CommissionRule::create([
            'name' => 'Global Rule',
            'type' => 'percentage',
            'value' => 10,
            'applies_to' => 'global',
            'is_active' => true,
            'priority' => 0,
        ]);

        CommissionRule::create([
            'name' => 'Seller Rule',
            'type' => 'percentage',
            'value' => 5,
            'applies_to' => 'seller',
            'seller_id' => $this->seller->id,
            'is_active' => true,
            'priority' => 10,
        ]);

        $order = $this->createPaidOrder(['total' => 100.00]);
        $record = $this->commissionService->calculateForOrder($order);

        $this->assertEquals(5.00, (float) $record->commission_amount);
        $this->assertEquals('Seller Rule', $record->commissionRule->name);
    }

    // --- Wallet Tests ---

    public function test_wallet_created_on_commission_credit(): void
    {
        $order = $this->createPaidOrder(['total' => 100.00]);

        $this->commissionService->calculateForOrder($order);

        $wallet = $this->walletService->getOrCreate($this->seller->id);
        $this->assertEquals(90.00, (float) $wallet->pending_balance);
        $this->assertEquals(0.00, (float) $wallet->available_balance);
        $this->assertEquals(90.00, (float) $wallet->total_earned);
    }

    public function test_wallet_transaction_recorded_immutable(): void
    {
        $order = $this->createPaidOrder(['total' => 100.00]);

        $this->commissionService->calculateForOrder($order);

        $tx = WalletTransaction::where('seller_id', $this->seller->id)->first();
        $this->assertNotNull($tx);
        $this->assertEquals('commission_credit', $tx->type);
        $this->assertEquals(90.00, (float) $tx->amount);
        $this->assertEquals(0.00, (float) $tx->balance_before);
        $this->assertEquals(90.00, (float) $tx->balance_after);
    }

    public function test_wallet_multiple_credits_accumulate(): void
    {
        $order1 = $this->createPaidOrder(['total' => 100.00]);
        $order2 = $this->createPaidOrder(['total' => 200.00]);

        $this->commissionService->calculateForOrder($order1);
        $this->commissionService->calculateForOrder($order2);

        $wallet = $this->walletService->getOrCreate($this->seller->id);
        $this->assertEquals(270.00, (float) $wallet->pending_balance); // 90 + 180
    }

    public function test_wallet_balance_helper(): void
    {
        $balance = $this->walletService->getBalance($this->seller->id);

        $this->assertEquals(0, $balance['pending']);
        $this->assertEquals(0, $balance['available']);
        $this->assertEquals(0, $balance['withdrawn']);
    }

    // --- Settlement Tests ---

    public function test_settlement_moves_pending_to_available(): void
    {
        $order = $this->createPaidOrder(['total' => 100.00]);
        $this->commissionService->calculateForOrder($order);

        $settlement = $this->settlementService->createSettlement($this->seller->id, 50.00);

        $this->assertNotNull($settlement);
        $this->assertEquals(50.00, (float) $settlement->amount);
        $this->assertEquals('pending', $settlement->status);

        $wallet = $this->walletService->getOrCreate($this->seller->id);
        $this->assertEquals(40.00, (float) $wallet->pending_balance);  // 90 - 50
        $this->assertEquals(50.00, (float) $wallet->available_balance);
    }

    public function test_settlement_fails_if_insufficient_balance(): void
    {
        $settlement = $this->settlementService->createSettlement($this->seller->id, 100.00);
        $this->assertNull($settlement);
    }

    public function test_settlement_creates_wallet_transaction(): void
    {
        $order = $this->createPaidOrder(['total' => 100.00]);
        $this->commissionService->calculateForOrder($order);

        $this->settlementService->createSettlement($this->seller->id, 50.00);

        $tx = WalletTransaction::where('seller_id', $this->seller->id)
            ->where('type', 'settlement_credit')
            ->first();

        $this->assertNotNull($tx);
        $this->assertEquals(50.00, (float) $tx->amount);
    }

    public function test_complete_settlement(): void
    {
        $order = $this->createPaidOrder(['total' => 100.00]);
        $this->commissionService->calculateForOrder($order);

        $settlement = $this->settlementService->createSettlement($this->seller->id, 50.00);
        $result = $this->settlementService->completeSettlement($settlement);

        $this->assertTrue($result);
        $settlement->refresh();
        $this->assertEquals('completed', $settlement->status);
        $this->assertNotNull($settlement->completed_at);
    }

    public function test_settle_all_pending(): void
    {
        $order = $this->createPaidOrder(['total' => 100.00]);
        $this->commissionService->calculateForOrder($order);

        $settlement = $this->settlementService->settleAllPending($this->seller->id);

        $this->assertNotNull($settlement);
        $this->assertEquals(90.00, (float) $settlement->amount);

        $wallet = $this->walletService->getOrCreate($this->seller->id);
        $this->assertEquals(0.00, (float) $wallet->pending_balance);
        $this->assertEquals(90.00, (float) $wallet->available_balance);
    }

    // --- Seller Controller Tests ---

    public function test_seller_can_view_commission_index(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.commission.index'));

        $response->assertOk();
        $response->assertViewIs('seller.commission.index');
    }

    public function test_seller_can_view_earnings(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.commission.earnings'));

        $response->assertOk();
        $response->assertViewIs('seller.commission.earnings');
    }

    public function test_seller_can_view_wallet(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.wallet.index'));

        $response->assertOk();
        $response->assertViewIs('seller.wallet.index');
    }

    public function test_seller_can_view_withdrawals(): void
    {
        $response = $this->actingAs($this->sellerUser)
            ->get(route('seller.withdrawals.index'));

        $response->assertOk();
        $response->assertViewIs('seller.wallet.settlements');
    }

    public function test_pending_seller_redirected_from_financial_pages(): void
    {
        $pendingUser = User::factory()->create();
        Seller::factory()->create(['user_id' => $pendingUser->id, 'status' => 'pending']);

        $response = $this->actingAs($pendingUser)->get(route('seller.commission.index'));
        $response->assertRedirect(route('seller.dashboard'));
    }

    // --- Financial Calculation Integrity ---

    public function test_financial_calculation_accuracy(): void
    {
        $order = $this->createPaidOrder(['total' => 333.33]);

        CommissionRule::create([
            'name' => '7.5% Rule',
            'type' => 'percentage',
            'value' => 7.5,
            'applies_to' => 'global',
            'is_active' => true,
        ]);

        $record = $this->commissionService->calculateForOrder($order);

        $this->assertEquals(25.00, round((float) $record->commission_amount, 2));
        $this->assertEquals(308.33, round((float) $record->seller_earnings, 2));
        $this->assertEquals(333.33, round((float) $record->order_amount, 2));

        // Verify: commission + earnings = order amount
        $this->assertEquals(
            round((float) $record->order_amount, 2),
            round((float) $record->commission_amount + (float) $record->seller_earnings, 2)
        );
    }

    public function test_commission_record_unique_per_order(): void
    {
        $order = $this->createPaidOrder();

        $this->commissionService->calculateForOrder($order);
        $this->commissionService->calculateForOrder($order);

        $this->assertEquals(1, CommissionRecord::where('order_id', $order->id)->count());
    }
}
