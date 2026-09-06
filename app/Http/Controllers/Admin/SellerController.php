<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CommissionRecord;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Settlement;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $query = Seller::with('user')
            ->withCount([
                'products',
                'orders',
            ]);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                  ->orWhere('store_slug', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $sort = $request->input('sort', 'latest');
        $query = match ($sort) {
            'oldest' => $query->oldest(),
            'name' => $query->orderBy('store_name'),
            'products' => $query->orderBy('products_count', 'desc'),
            'orders' => $query->orderBy('orders_count', 'desc'),
            default => $query->latest(),
        };

        $sellers = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Seller::count(),
            'pending' => Seller::pending()->count(),
            'approved' => Seller::approved()->count(),
            'rejected' => Seller::where('status', 'rejected')->count(),
            'suspended' => Seller::where('status', 'suspended')->count(),
        ];

        return view('admin.sellers.index', compact('sellers', 'stats'));
    }

    public function pending(Request $request)
    {
        $query = Seller::with('user')->pending()->withCount('products');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $sellers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.sellers.pending', compact('sellers'));
    }

    public function performance(Request $request)
    {
        $query = Seller::with('user')
            ->approved()
            ->withCount(['products', 'orders']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $sellers = $query->get();

        foreach ($sellers as $seller) {
            $orders = $seller->orders();
            $paidOrders = $orders->clone()->where('payment_status', 'paid');

            $seller->total_sales = (clone $paidOrders)->sum('total');
            $seller->avg_order_value = $paidOrders->count() > 0 ? $seller->total_sales / $paidOrders->count() : 0;
            $seller->total_commission = $seller->commissionRecords()->where('status', '!=', 'cancelled')->sum('commission_amount');
            $seller->pending_balance = $seller->wallet?->pending_balance ?? 0;
            $seller->available_balance = $seller->wallet?->available_balance ?? 0;
            $seller->paid_orders_count = $paidOrders->count();
        }

        $sellers = $sellers->sortByDesc('total_sales')->values();

        return view('admin.sellers.performance', compact('sellers'));
    }

    public function show(Seller $seller)
    {
        $seller->load(['user', 'staffDetails', 'wallet']);

        $stats = [
            'products_count' => $seller->products()->count(),
            'published_products' => $seller->products()->where('status', 'published')->count(),
            'pending_products' => $seller->products()->where('status', 'pending_review')->count(),
            'draft_products' => $seller->products()->where('status', 'draft')->count(),
            'orders_count' => $seller->orders()->count(),
            'total_sales' => $seller->orders()->where('payment_status', 'paid')->sum('total'),
            'total_commission' => $seller->commissionRecords()->where('status', '!=', 'cancelled')->sum('commission_amount'),
            'pending_commission' => $seller->commissionRecords()->pending()->sum('commission_amount'),
            'settled_commission' => $seller->commissionRecords()->where('status', 'settled')->sum('commission_amount'),
        ];

        $recentOrders = $seller->orders()->with('user')->latest()->limit(10)->get();
        $recentProducts = $seller->products()->latest()->limit(10)->get();
        $recentActivity = ActivityLog::where('subject_type', Seller::class)
            ->where('subject_id', $seller->id)
            ->with('user')
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.sellers.show', compact('seller', 'stats', 'recentOrders', 'recentProducts', 'recentActivity'));
    }

    public function approve(Seller $seller)
    {
        if (!$seller->isPending()) {
            return back()->with('error', 'Only pending sellers can be approved.');
        }

        $seller->approve();

        $seller->user->roles()->syncWithoutDetaching(
            \App\Models\Role::where('slug', 'seller')->pluck('id')
        );

        ActivityLog::log(
            'seller.approved',
            "Seller \"{$seller->store_name}\" was approved.",
            $seller,
            ['previous_status' => 'pending', 'new_status' => 'approved']
        );

        return back()->with('success', 'Seller "' . $seller->store_name . '" has been approved.');
    }

    public function reject(Request $request, Seller $seller)
    {
        if (!$seller->isPending()) {
            return back()->with('error', 'Only pending sellers can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $seller->reject($validated['rejection_reason']);

        ActivityLog::log(
            'seller.rejected',
            "Seller \"{$seller->store_name}\" was rejected. Reason: {$validated['rejection_reason']}",
            $seller,
            ['previous_status' => 'pending', 'new_status' => 'rejected', 'reason' => $validated['rejection_reason']]
        );

        return back()->with('success', 'Seller "' . $seller->store_name . '" has been rejected.');
    }

    public function suspend(Request $request, Seller $seller)
    {
        if (!$seller->isApproved()) {
            return back()->with('error', 'Only approved sellers can be suspended.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $seller->suspend($validated['rejection_reason']);

        ActivityLog::log(
            'seller.suspended',
            "Seller \"{$seller->store_name}\" was suspended. Reason: {$validated['rejection_reason']}",
            $seller,
            ['previous_status' => 'approved', 'new_status' => 'suspended', 'reason' => $validated['rejection_reason']]
        );

        return back()->with('success', 'Seller "' . $seller->store_name . '" has been suspended.');
    }

    public function activate(Seller $seller)
    {
        if (!$seller->isSuspended() && !$seller->isRejected()) {
            return back()->with('error', 'Only suspended or rejected sellers can be activated.');
        }

        $previousStatus = $seller->status;
        $seller->update(['status' => 'approved', 'rejection_reason' => null, 'approved_at' => $seller->approved_at ?? now()]);

        ActivityLog::log(
            'seller.activated',
            "Seller \"{$seller->store_name}\" was reactivated.",
            $seller,
            ['previous_status' => $previousStatus, 'new_status' => 'approved']
        );

        return back()->with('success', 'Seller "' . $seller->store_name . '" has been reactivated.');
    }

    public function products(Request $request, Seller $seller)
    {
        $seller->load('user');

        $query = $seller->products()->with('category');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->latest()->paginate(20)->withQueryString();

        return view('admin.sellers.products', compact('seller', 'products'));
    }

    public function orders(Request $request, Seller $seller)
    {
        $seller->load('user');

        $query = $seller->orders()->with('user');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where('order_number', 'like', "%{$search}%");
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        return view('admin.sellers.orders', compact('seller', 'orders'));
    }

    public function finance(Seller $seller)
    {
        $seller->load(['user', 'wallet']);

        $commissionRecords = $seller->commissionRecords()->with('order')->latest()->paginate(15)->withQueryString();
        $settlements = $seller->settlements()->latest()->paginate(15)->withQueryString();

        $walletTransactions = collect();
        if ($seller->wallet) {
            $walletTransactions = WalletTransaction::where('seller_id', $seller->id)
                ->latest()
                ->paginate(15)
                ->withQueryString();
        }

        $stats = [
            'total_commission' => $seller->commissionRecords()->where('status', '!=', 'cancelled')->sum('commission_amount'),
            'pending_commission' => $seller->commissionRecords()->pending()->sum('commission_amount'),
            'settled_commission' => $seller->commissionRecords()->where('status', 'settled')->sum('commission_amount'),
            'wallet_pending' => $seller->wallet?->pending_balance ?? 0,
            'wallet_available' => $seller->wallet?->available_balance ?? 0,
            'wallet_withdrawn' => $seller->wallet?->withdrawn_amount ?? 0,
            'total_settlements' => $seller->settlements()->where('status', 'completed')->sum('amount'),
            'pending_settlements' => $seller->settlements()->where('status', 'pending')->sum('amount'),
        ];

        return view('admin.sellers.finance', compact('seller', 'commissionRecords', 'settlements', 'walletTransactions', 'stats'));
    }

    public function activityLog(Seller $seller)
    {
        $seller->load('user');

        $activities = ActivityLog::where('subject_type', Seller::class)
            ->where('subject_id', $seller->id)
            ->with('user')
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.sellers.activity', compact('seller', 'activities'));
    }
}
