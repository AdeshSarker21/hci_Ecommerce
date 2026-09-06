<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SellerOrderController extends Controller
{
    public function index(Request $request)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $query = Order::where('seller_id', $seller->id)
            ->with(['user:id,name,email', 'items' => function ($q) {
                $q->select('order_id', 'product_name', 'quantity', 'total');
            }]);

        // Search
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by payment status
        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        // Filter by date range
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        $query = match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'total_high' => $query->orderBy('total', 'desc'),
            'total_low' => $query->orderBy('total', 'asc'),
            'status' => $query->orderBy('status'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $orders = $query->paginate(15)->withQueryString();

        // Stats for sidebar
        $stats = [
            'total' => Order::where('seller_id', $seller->id)->count(),
            'pending' => Order::where('seller_id', $seller->id)->where('status', 'pending')->count(),
            'processing' => Order::where('seller_id', $seller->id)->where('status', 'processing')->count(),
            'shipped' => Order::where('seller_id', $seller->id)->where('status', 'shipped')->count(),
            'delivered' => Order::where('seller_id', $seller->id)->where('status', 'delivered')->count(),
            'cancelled' => Order::where('seller_id', $seller->id)->where('status', 'cancelled')->count(),
        ];

        return view('seller.orders.index', compact('orders', 'stats', 'seller'));
    }

    public function show(Order $order)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $order->seller_id !== $seller->id) {
            abort(403);
        }

        $order->load([
            'user:id,name,email,phone',
            'items.product:id,name,slug,sku,price,image',
            'statusHistory.user:id,name',
        ]);

        $allowedTransitions = $order->getAllowedStatusTransitions();

        return view('seller.orders.show', compact('order', 'seller', 'allowedTransitions'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $order->seller_id !== $seller->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:processing,shipped,delivered,cancelled',
            'note' => 'nullable|string|max:500',
            'tracking_number' => 'nullable|string|max:100',
            'courier_name' => 'nullable|string|max:100',
            'tracking_url' => 'nullable|url|max:500',
        ]);

        $allowed = $order->getAllowedStatusTransitions();
        if (!in_array($validated['status'], $allowed)) {
            return back()->withErrors(['status' => 'Cannot transition from "' . $order->status . '" to "' . $validated['status'] . '".']);
        }

        $fromStatus = $order->status;
        $newStatus = $validated['status'];

        DB::beginTransaction();
        try {
            $updateData = ['status' => $newStatus];

            // Set timestamp based on status
            match ($newStatus) {
                'processing' => $updateData['processing_at'] = now(),
                'shipped' => $updateData['shipped_at'] = now(),
                'delivered' => $updateData['delivered_at'] = now(),
                'cancelled' => $updateData['cancelled_at'] = now(),
                default => null,
            };

            // Update tracking info if provided
            if (!empty($validated['tracking_number'])) {
                $updateData['tracking_number'] = $validated['tracking_number'];
            }
            if (!empty($validated['courier_name'])) {
                $updateData['courier_name'] = $validated['courier_name'];
            }
            if (!empty($validated['tracking_url'])) {
                $updateData['tracking_url'] = $validated['tracking_url'];
            }

            $order->update($updateData);

            // Log status change
            $order->logStatusChange(
                toStatus: $newStatus,
                fromStatus: $fromStatus,
                userId: auth()->id(),
                note: $validated['note'] ?? null,
                type: 'status'
            );

            // Activity log
            Log::info("Order #{$order->order_number} status changed from {$fromStatus} to {$newStatus} by seller {$seller->store_name}", [
                'order_id' => $order->id,
                'seller_id' => $seller->id,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return back()->with('success', "Order status updated to {$newStatus}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['status' => 'Failed to update order status.']);
        }
    }

    public function addNote(Request $request, Order $order)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $order->seller_id !== $seller->id) {
            abort(403);
        }

        $validated = $request->validate([
            'seller_notes' => 'required|string|max:1000',
        ]);

        $order->update(['seller_notes' => $validated['seller_notes']]);

        $order->logStatusChange(
            toStatus: $order->status,
            fromStatus: null,
            userId: auth()->id(),
            note: $validated['seller_notes'],
            type: 'note'
        );

        return back()->with('success', 'Note saved.');
    }

    public function updateTracking(Request $request, Order $order)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $order->seller_id !== $seller->id) {
            abort(403);
        }

        $validated = $request->validate([
            'tracking_number' => 'required|string|max:100',
            'courier_name' => 'required|string|max:100',
            'tracking_url' => 'nullable|url|max:500',
        ]);

        $order->update($validated);

        $order->logStatusChange(
            toStatus: $order->status,
            fromStatus: null,
            userId: auth()->id(),
            note: "Tracking updated: {$validated['courier_name']} - {$validated['tracking_number']}",
            type: 'shipment'
        );

        return back()->with('success', 'Tracking information updated.');
    }

    public function bulkUpdate(Request $request)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return back()->withErrors(['error' => 'Unauthorized.']);
        }

        $validated = $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|in:processing,shipped,delivered,cancelled',
        ]);

        $orders = Order::where('seller_id', $seller->id)
            ->whereIn('id', $validated['order_ids'])
            ->get();

        $updated = 0;
        foreach ($orders as $order) {
            $allowed = $order->getAllowedStatusTransitions();
            if (in_array($validated['status'], $allowed)) {
                $fromStatus = $order->status;
                $order->update(['status' => $validated['status']]);

                match ($validated['status']) {
                    'processing' => $order->update(['processing_at' => now()]),
                    'shipped' => $order->update(['shipped_at' => now()]),
                    'delivered' => $order->update(['delivered_at' => now()]),
                    'cancelled' => $order->update(['cancelled_at' => now()]),
                    default => null,
                };

                $order->logStatusChange(
                    toStatus: $validated['status'],
                    fromStatus: $fromStatus,
                    userId: auth()->id(),
                    note: 'Bulk status update',
                    type: 'status'
                );
                $updated++;
            }
        }

        return back()->with('success', "{$updated} order(s) updated to {$validated['status']}.");
    }
}
