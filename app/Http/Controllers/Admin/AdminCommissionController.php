<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionRecord;
use App\Models\CommissionRule;
use App\Models\Settlement;
use App\Models\Seller;
use App\Services\CommissionService;
use App\Services\SettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCommissionController extends Controller
{
    public function rules(Request $request)
    {
        $query = CommissionRule::with(['seller', 'category', 'product']);

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($appliesTo = $request->input('applies_to')) {
            $query->where('applies_to', $appliesTo);
        }

        $rules = $query->orderByDesc('priority')->latest()->paginate(15)->withQueryString();

        return view('admin.commission.rules', compact('rules'));
    }

    public function storeRule(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'applies_to' => 'required|in:global,seller,category,product',
            'seller_id' => 'nullable|required_if:applies_to,seller|exists:sellers,id',
            'category_id' => 'nullable|required_if:applies_to,category|exists:categories,id',
            'product_id' => 'nullable|required_if:applies_to,product|exists:products,id',
            'priority' => 'integer|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        CommissionRule::create($validated);

        return back()->with('success', 'Commission rule created.');
    }

    public function updateRule(Request $request, CommissionRule $rule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $rule->update($validated);

        return back()->with('success', 'Commission rule updated.');
    }

    public function destroyRule(CommissionRule $rule)
    {
        $rule->delete();
        return back()->with('success', 'Commission rule deleted.');
    }

    public function records(Request $request)
    {
        $query = CommissionRecord::with(['order:id,order_number,created_at', 'seller:id,store_name', 'commissionRule:id,name']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($sellerId = $request->input('seller_id')) {
            $query->where('seller_id', $sellerId);
        }

        $records = $query->latest()->paginate(15)->withQueryString();
        $sellers = Seller::approved()->get();

        return view('admin.commission.records', compact('records', 'sellers'));
    }

    public function settlements(Request $request)
    {
        $query = Settlement::with(['seller:id,store_name', 'wallet']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $settlements = $query->latest()->paginate(15)->withQueryString();

        return view('admin.commission.settlements', compact('settlements'));
    }

    public function completeSettlement(Settlement $settlement)
    {
        app(SettlementService::class)->completeSettlement($settlement);
        return back()->with('success', 'Settlement completed.');
    }

    public function processOrderCommission(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = \App\Models\Order::find($validated['order_id']);

        if ($order->payment_status !== 'paid') {
            return back()->withErrors(['order_id' => 'Order must be paid.']);
        }

        $existing = CommissionRecord::where('order_id', $order->id)->first();
        if ($existing) {
            return back()->withErrors(['order_id' => 'Commission already recorded for this order.']);
        }

        app(CommissionService::class)->calculateForOrder($order);

        return back()->with('success', 'Commission processed for order #{$order->order_number}.');
    }

    public function createSettlement(Request $request)
    {
        $validated = $request->validate([
            'seller_id' => 'required|exists:sellers,id',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $settlement = app(SettlementService::class)->createSettlement(
            $validated['seller_id'],
            $validated['amount'],
            $validated['notes'] ?? null
        );

        if (!$settlement) {
            return back()->withErrors(['amount' => 'Insufficient pending balance.']);
        }

        return back()->with('success', "Settlement #{$settlement->settlement_number} created.");
    }
}
