<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $period = $request->input('period', '30d');
        $dateRange = $this->getDateRange($period);

        $stats = $this->getStats($seller->id, $dateRange);
        $salesData = $this->getSalesData($seller->id, $period);
        $recentOrders = $this->getRecentOrders($seller->id);
        $topProducts = $this->getTopProducts($seller->id, $dateRange);
        $lowStockProducts = $this->getLowStockProducts($seller->id);
        $pendingProducts = $this->getPendingProducts($seller->id);
        $recentActivity = $this->getRecentActivity($seller->id);
        $performance = $this->getPerformance($seller->id);

        // Wallet data for earnings/payout stats
        $wallet = $seller->wallet;
        $totalEarnings = $wallet ? (float) $wallet->total_earned : 0;
        $pendingPayout = $wallet ? (float) $wallet->pending_balance : 0;
        $availableBalance = $wallet ? (float) $wallet->available_balance : 0;

        return view('seller.dashboard-analytics', compact(
            'seller', 'stats', 'salesData', 'recentOrders',
            'topProducts', 'lowStockProducts', 'pendingProducts',
            'recentActivity', 'performance', 'period',
            'totalEarnings', 'pendingPayout', 'availableBalance'
        ));
    }

    private function getDateRange(string $period): array
    {
        return match ($period) {
            '7d' => [now()->subDays(7), now()],
            '30d' => [now()->subDays(30), now()],
            '90d' => [now()->subDays(90), now()],
            '12m' => [now()->subYear(), now()],
            'all' => [Carbon::parse('2000-01-01'), now()],
            default => [now()->subDays(30), now()],
        };
    }

    private function getStats(int $sellerId, array $dateRange): array
    {
        $orderQuery = Order::where('seller_id', $sellerId)
            ->whereBetween('created_at', $dateRange);

        $totalRevenue = (clone $orderQuery)->where('payment_status', 'paid')->sum('total');
        $totalOrders = (clone $orderQuery)->count();
        $completedOrders = (clone $orderQuery)->whereIn('status', ['delivered', 'completed'])->count();
        $pendingOrders = (clone $orderQuery)->where('status', 'pending')->count();
        $cancelledOrders = (clone $orderQuery)->where('status', 'cancelled')->count();

        $prevDateRange = [
            $dateRange[0]->copy()->subtract($dateRange[0]->diffInDays($dateRange[1]) + 1, 'days'),
            $dateRange[0]->copy()->subDay(),
        ];
        $prevRevenue = Order::where('seller_id', $sellerId)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', $prevDateRange)
            ->sum('total');
        $prevOrders = Order::where('seller_id', $sellerId)
            ->whereBetween('created_at', $prevDateRange)
            ->count();

        $revenueChange = $prevRevenue > 0 ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1) : 0;
        $ordersChange = $prevOrders > 0 ? round((($totalOrders - $prevOrders) / $prevOrders) * 100, 1) : 0;

        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;
        $conversionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'pending_orders' => $pendingOrders,
            'cancelled_orders' => $cancelledOrders,
            'avg_order_value' => $avgOrderValue,
            'conversion_rate' => $conversionRate,
            'revenue_change' => $revenueChange,
            'orders_change' => $ordersChange,
            'total_products' => Product::where('seller_id', $sellerId)->count(),
            'published_products' => Product::where('seller_id', $sellerId)->where('status', 'published')->count(),
            'pending_products' => Product::where('seller_id', $sellerId)->where('status', 'pending_review')->count(),
            'low_stock_count' => Product::where('seller_id', $sellerId)
                ->where('manage_stock', true)
                ->where('quantity', '>', 0)
                ->whereColumn('quantity', '<=', 'low_stock_threshold')
                ->count(),
        ];
    }

    private function getSalesData(int $sellerId, string $period): array
    {
        $days = match ($period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            '12m' => 365,
            default => 30,
        };

        $startDate = now()->subDays($days);

        $dailySales = Order::where('seller_id', $sellerId)
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $allDates = collect();
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $allDates[$date] = ['date' => $date, 'order_count' => 0, 'revenue' => 0];
        }

        foreach ($dailySales as $sale) {
            $date = $sale->date instanceof Carbon ? $sale->date->format('Y-m-d') : $sale->date;
            $allDates[$date] = [
                'date' => $date,
                'order_count' => (int) $sale->order_count,
                'revenue' => (float) $sale->revenue,
            ];
        }

        return $allDates->values()->toArray();
    }

    private function getRecentOrders(int $sellerId): \Illuminate\Database\Eloquent\Collection
    {
        return Order::where('seller_id', $sellerId)
            ->with(['user:id,name,email', 'items' => function ($q) {
                $q->with('product:id,name,slug');
            }])
            ->latest()
            ->limit(10)
            ->get();
    }

    private function getTopProducts(int $sellerId, array $dateRange): \Illuminate\Database\Eloquent\Collection
    {
        $productIds = OrderItem::whereHas('order', function ($q) use ($sellerId, $dateRange) {
                $q->where('orders.seller_id', $sellerId)
                  ->whereBetween('orders.created_at', $dateRange)
                  ->where('orders.payment_status', 'paid');
            })
            ->select('product_id')
            ->selectRaw('SUM(quantity) as total_quantity, SUM(total) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->pluck('product_id');

        return Product::whereIn('id', $productIds)
            ->with(['images' => function ($q) {
                $q->where('is_featured', true)->limit(1);
            }])
            ->get()
            ->map(function ($product) use ($productIds) {
                $stats = OrderItem::where('product_id', $product->id)
                    ->whereHas('order', function ($q) {
                        $q->where('orders.payment_status', 'paid');
                    })
                    ->selectRaw('SUM(quantity) as total_quantity, SUM(total) as total_revenue')
                    ->first();
                $product->total_orders = $stats->total_quantity ?? 0;
                $product->total_revenue = $stats->total_revenue ?? 0;
                return $product;
            });
    }

    private function getLowStockProducts(int $sellerId): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('seller_id', $sellerId)
            ->where('manage_stock', true)
            ->where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->with(['images' => function ($q) {
                $q->where('is_featured', true)->limit(1);
            }])
            ->orderBy('quantity')
            ->limit(5)
            ->get();
    }

    private function getPendingProducts(int $sellerId): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('seller_id', $sellerId)
            ->where('status', 'pending_review')
            ->with(['category', 'images' => function ($q) {
                $q->where('is_featured', true)->limit(1);
            }])
            ->latest()
            ->limit(5)
            ->get();
    }

    private function getRecentActivity(int $sellerId): array
    {
        $orders = Order::where('seller_id', $sellerId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($order) {
                $statusText = match ($order->status) {
                    'pending' => 'New order received',
                    'processing' => 'Order is being processed',
                    'shipped' => 'Order has been shipped',
                    'delivered' => 'Order has been delivered',
                    'cancelled' => 'Order was cancelled',
                    default => 'Order status updated',
                };

                return [
                    'type' => 'order',
                    'icon' => 'shopping-cart',
                    'color' => match ($order->status) {
                        'pending' => 'yellow',
                        'processing' => 'blue',
                        'shipped' => 'indigo',
                        'delivered' => 'green',
                        'cancelled' => 'red',
                        default => 'gray',
                    },
                    'message' => "{$statusText}: #{$order->order_number}",
                    'detail' => '$' . number_format($order->total, 2),
                    'time' => $order->created_at->diffForHumans(),
                ];
            });

        $products = Product::where('seller_id', $sellerId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($product) {
                $statusText = match ($product->status) {
                    'draft' => 'Product created as draft',
                    'pending_review' => 'Product submitted for review',
                    'approved' => 'Product approved',
                    'published' => 'Product published',
                    'rejected' => 'Product rejected',
                    default => 'Product updated',
                };

                return [
                    'type' => 'product',
                    'icon' => 'cube',
                    'color' => match ($product->status) {
                        'draft' => 'gray',
                        'pending_review' => 'yellow',
                        'approved' => 'blue',
                        'published' => 'green',
                        'rejected' => 'red',
                        default => 'gray',
                    },
                    'message' => "{$statusText}: {$product->name}",
                    'detail' => $product->sku,
                    'time' => $product->created_at->diffForHumans(),
                ];
            });

        return $orders->concat($products)
            ->sortByDesc('time')
            ->take(10)
            ->values()
            ->toArray();
    }

    private function getPerformance(int $sellerId): array
    {
        $seller = auth()->user()->seller;

        $totalSales = Order::where('seller_id', $sellerId)
            ->where('payment_status', 'paid')
            ->sum('total');

        $completedOrders = Order::where('seller_id', $sellerId)
            ->whereIn('status', ['delivered', 'completed'])
            ->count();

        $totalOrders = Order::where('seller_id', $sellerId)->count();

        $onTimeDelivery = Order::where('seller_id', $sellerId)
            ->where('status', 'delivered')
            ->where(function ($q) {
                $q->whereNull('shipped_at')
                  ->orWhere('shipped_at', '<=', DB::raw("DATE_ADD(orders.created_at, INTERVAL 3 DAY)"));
            })
            ->count();

        $responseRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;
        $onTimeRate = $completedOrders > 0 ? round(($onTimeDelivery / $completedOrders) * 100, 1) : 0;

        return [
            'average_rating' => $seller->average_rating ?? 0,
            'total_reviews' => $seller->total_reviews ?? 0,
            'total_sales' => $totalSales,
            'completed_orders' => $completedOrders,
            'response_rate' => $responseRate,
            'on_time_delivery_rate' => $onTimeRate,
        ];
    }
}
