<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionRecord;
use App\Models\CourierCollection;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Settlement;
use App\Models\Wallet;
use App\Services\SettlementService;
use Illuminate\Http\Request;

class AdminSellerPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['seller:id,store_name', 'user:id,name,email', 'items', 'commissionRecord'])
            ->where('payment_status', 'paid')
            ->whereNotNull('seller_payment_status');

        if ($sellerId = $request->input('seller_id')) {
            $query->where('seller_id', $sellerId);
        }

        if ($sellerPaymentStatus = $request->input('seller_payment_status')) {
            $query->where('seller_payment_status', $sellerPaymentStatus);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('seller', fn ($sq) => $sq->where('store_name', 'like', "%{$search}%"))
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $orders = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $sellers = Seller::approved()->get();

        $stats = $this->getPaymentStats($request);

        return view('admin.seller-payments.index', compact('orders', 'sellers', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load([
            'seller:id,store_name',
            'user:id,name,email,phone',
            'items.product:id,name,slug,sku,price',
            'statusHistory.user:id,name',
            'courierCollection',
        ]);

        $commissionRecord = CommissionRecord::with(['commissionRule:id,name,type,value'])
            ->where('order_id', $order->id)
            ->first();

        $wallet = Wallet::with(['transactions' => function ($q) use ($order) {
            $q->where('reference_type', CommissionRecord::class)
              ->where('reference_id', $order->commissionRecord?->id ?? 0)
              ->orWhere(function ($q2) use ($order) {
                  $q2->where('description', 'like', "%order #{$order->order_number}%");
              });
        }])->where('seller_id', $order->seller_id)->first();

        $settlement = null;
        if ($commissionRecord) {
            $settlement = Settlement::where('seller_id', $order->seller_id)
                ->where('created_at', '>=', $commissionRecord->created_at)
                ->first();
        }

        return view('admin.seller-payments.show', compact('order', 'commissionRecord', 'wallet', 'settlement'));
    }

    public function storeSettlement(Request $request)
    {
        $validated = $request->validate([
            'seller_id' => 'required|exists:sellers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $settlement = app(SettlementService::class)->createSettlement(
            $validated['seller_id'],
            $validated['amount'],
            $validated['notes'] ?? null
        );

        if (!$settlement) {
            return back()->withErrors(['amount' => 'Insufficient pending balance or invalid amount.']);
        }

        if (!empty($validated['payment_method']) || !empty($validated['reference_number'])) {
            $settlement->update([
                'payment_method' => $validated['payment_method'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'status' => 'processing',
            ]);
        }

        return back()->with('success', "Settlement #{$settlement->settlement_number} created successfully.");
    }

    public function completeSettlement(Settlement $settlement)
    {
        $result = app(SettlementService::class)->completeSettlement($settlement);

        if (!$result) {
            return back()->withErrors(['error' => 'Settlement cannot be completed.']);
        }

        return back()->with('success', "Settlement #{$settlement->settlement_number} completed.");
    }

    public function sellerFinance(Seller $seller)
    {
        $wallet = $seller->wallet()->with(['transactions' => fn ($q) => $q->latest()->limit(20)])->first();

        $commissionRecords = CommissionRecord::with(['order:id,order_number,created_at'])
            ->where('seller_id', $seller->id)
            ->latest()
            ->paginate(15);

        $settlements = Settlement::where('seller_id', $seller->id)
            ->latest()
            ->paginate(15);

        $orderStatusCounts = Order::where('seller_id', $seller->id)
            ->where('payment_status', 'paid')
            ->whereNotNull('seller_payment_status')
            ->selectRaw('seller_payment_status, count(*) as count')
            ->groupBy('seller_payment_status')
            ->pluck('count', 'seller_payment_status');

        $stats = [
            'total_commission' => CommissionRecord::where('seller_id', $seller->id)->sum('commission_amount'),
            'total_earnings' => CommissionRecord::where('seller_id', $seller->id)->sum('seller_earnings'),
            'pending_commission' => CommissionRecord::where('seller_id', $seller->id)->where('status', 'pending')->sum('seller_earnings'),
            'settled_commission' => CommissionRecord::where('seller_id', $seller->id)->where('status', 'settled')->sum('seller_earnings'),
            'total_orders' => Order::where('seller_id', $seller->id)->where('payment_status', 'paid')->count(),
            'total_settled' => Settlement::where('seller_id', $seller->id)->where('status', 'completed')->sum('amount'),
            'order_status_counts' => $orderStatusCounts,
        ];

        return view('admin.seller-payments.seller-finance', compact('seller', 'wallet', 'commissionRecords', 'settlements', 'stats'));
    }

    private function getPaymentStats(Request $request): array
    {
        $baseQuery = Order::where('payment_status', 'paid')->whereNotNull('seller_payment_status');

        if ($sellerId = $request->input('seller_id')) {
            $baseQuery->where('seller_id', $sellerId);
        }

        $totalOrders = (clone $baseQuery)->count();
        $totalRevenue = (clone $baseQuery)->sum('total');

        $pendingCollection = (clone $baseQuery)->where('seller_payment_status', 'pending_collection')->count();
        $collectedByCourier = (clone $baseQuery)->where('seller_payment_status', 'collected_by_courier')->count();
        $awaitingSettlement = (clone $baseQuery)->where('seller_payment_status', 'awaiting_settlement')->count();
        $availableForPayout = (clone $baseQuery)->where('seller_payment_status', 'available_for_payout')->count();
        $payoutProcessing = (clone $baseQuery)->where('seller_payment_status', 'payout_processing')->count();
        $paid = (clone $baseQuery)->where('seller_payment_status', 'paid')->count();

        $availableBalance = Wallet::when($request->input('seller_id'), fn ($q) => $q->where('seller_id', $request->input('seller_id')))
            ->sum('available_balance');

        $totalPaid = Wallet::when($request->input('seller_id'), fn ($q) => $q->where('seller_id', $request->input('seller_id')))
            ->sum('withdrawn_amount');

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'pending_collection' => $pendingCollection,
            'collected_by_courier' => $collectedByCourier,
            'awaiting_settlement' => $awaitingSettlement,
            'available_for_payout' => $availableForPayout,
            'payout_processing' => $payoutProcessing,
            'paid' => $paid,
            'available_balance' => $availableBalance,
            'total_paid' => $totalPaid,
        ];
    }
}
