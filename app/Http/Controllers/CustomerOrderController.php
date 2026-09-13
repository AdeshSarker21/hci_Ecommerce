<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerOrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $orders = Order::where('user_id', $user->id)
            ->with(['items.product', 'seller'])
            ->withCount('items')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Order::where('user_id', $user->id)->count(),
            'pending' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'processing' => Order::where('user_id', $user->id)->where('status', 'processing')->count(),
            'shipped' => Order::where('user_id', $user->id)->where('status', 'shipped')->count(),
            'delivered' => Order::where('user_id', $user->id)->where('status', 'delivered')->count(),
        ];

        return view('account.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order): View|RedirectResponse
    {
        $user = auth()->user();

        if ($order->user_id !== $user->id) {
            abort(403, __('You are not authorized to view this order.'));
        }

        $order->load([
            'items.product.images',
            'seller',
            'statusHistory' => fn ($q) => $q->latest()->limit(20),
        ]);

        $timeline = $this->buildTimeline($order);

        return view('account.orders.show', compact('order', 'timeline'));
    }

    public function confirmation(Order $order): View|RedirectResponse
    {
        $user = auth()->user();

        if ($order->user_id !== $user->id) {
            abort(403, __('You are not authorized to view this order.'));
        }

        $order->load([
            'items.product.images',
            'seller',
        ]);

        return view('account.orders.confirmation', compact('order'));
    }

    protected function buildTimeline(Order $order): array
    {
        $steps = [
            ['key' => 'pending', 'label' => __('Order Placed'), 'icon' => 'receipt'],
            ['key' => 'processing', 'label' => __('Confirmed'), 'icon' => 'check-circle'],
            ['key' => 'shipped', 'label' => __('Shipped'), 'icon' => 'truck'],
            ['key' => 'delivered', 'label' => __('Delivered'), 'icon' => 'home'],
        ];

        $statusOrder = ['pending', 'processing', 'shipped', 'delivered'];
        $currentIndex = array_search($order->status, $statusOrder);
        $currentIndex = $currentIndex === false ? -1 : $currentIndex;

        foreach ($steps as $index => &$step) {
            $step['completed'] = $index <= $currentIndex;
            $step['current'] = $index === $currentIndex;
            $step['timestamp'] = match ($step['key']) {
                'pending' => $order->created_at,
                'processing' => $order->processing_at,
                'shipped' => $order->shipped_at,
                'delivered' => $order->delivered_at,
                default => null,
            };
        }

        if ($order->status === 'cancelled') {
            $steps[] = [
                'key' => 'cancelled',
                'label' => __('Cancelled'),
                'icon' => 'x-circle',
                'completed' => true,
                'current' => true,
                'timestamp' => $order->cancelled_at,
            ];
        }

        return $steps;
    }
}
