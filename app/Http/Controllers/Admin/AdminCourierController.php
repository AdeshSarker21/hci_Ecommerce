<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\CourierCollection;
use App\Models\Seller;
use App\Services\CourierCollectionSyncService;
use App\Services\ReconciliationService;
use App\Services\SettlementService;
use Illuminate\Http\Request;

class AdminCourierController extends Controller
{
    public function index(Request $request)
    {
        $query = CourierCollection::with(['order:id,order_number,created_at,total', 'seller:id,store_name', 'courier:id,name,slug']);

        if ($sellerId = $request->input('seller_id')) {
            $query->where('seller_id', $sellerId);
        }

        if ($courierId = $request->input('courier_id')) {
            $query->where('courier_id', $courierId);
        }

        if ($status = $request->input('status')) {
            $query->where('collection_status', $status);
        }

        if ($settlementStatus = $request->input('settlement_status')) {
            $query->where('settlement_status', $settlementStatus);
        }

        if ($reconciliationStatus = $request->input('reconciliation_status')) {
            $query->where('reconciliation_status', $reconciliationStatus);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('consignment_id', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"))
                  ->orWhereHas('seller', fn ($sq) => $sq->where('store_name', 'like', "%{$search}%"));
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $collections = $query->latest()->paginate(15)->withQueryString();
        $sellers = Seller::approved()->get();
        $couriers = Courier::active()->orderBy('sort_order')->get();

        $stats = [
            'total_collections' => CourierCollection::count(),
            'pending' => CourierCollection::where('collection_status', 'pending')->count(),
            'collected' => CourierCollection::where('collection_status', 'collected')->count(),
            'confirmed' => CourierCollection::where('collection_status', 'confirmed')->count(),
            'total_pending_cod' => CourierCollection::where('collection_status', 'pending')->sum('cod_amount'),
            'settlement_pending' => CourierCollection::where('settlement_status', 'pending')->count(),
            'settled' => CourierCollection::where('settlement_status', 'settled')->count(),
            'total_settled_amount' => CourierCollection::where('settlement_status', 'settled')->sum('seller_earning'),
            'reconciliation_pending' => CourierCollection::where('reconciliation_status', 'pending')->count(),
            'reconciled' => CourierCollection::where('reconciliation_status', 'reconciled')->count(),
            'discrepancy' => CourierCollection::where('reconciliation_status', 'discrepancy')->count(),
            'total_discrepancy' => CourierCollection::whereNotNull('discrepancy_amount')->sum('discrepancy_amount'),
        ];

        return view('admin.courier-collections.index', compact('collections', 'sellers', 'couriers', 'stats'));
    }

    public function show(CourierCollection $collection)
    {
        $collection->load([
            'order:id,order_number,created_at,total,payment_method,payment_status',
            'seller:id,store_name,user_id',
            'courier:id,name,slug',
            'reconciledByUser:id,name',
            'shipment:id,consignment_id,shipment_number,status',
        ]);

        return view('admin.courier-collections.show', compact('collection'));
    }

    public function confirm(CourierCollection $collection)
    {
        $result = app(SettlementService::class)->confirmCourierCollection($collection);

        if (!$result) {
            return back()->withErrors(['error' => 'Collection cannot be confirmed.']);
        }

        return back()->with('success', "Courier collection confirmed. Order is now awaiting settlement.");
    }

    public function confirmBulk(Request $request)
    {
        $validated = $request->validate([
            'collection_ids' => 'required|array',
            'collection_ids.*' => 'exists:courier_collections,id',
        ]);

        $collections = CourierCollection::whereIn('id', $validated['collection_ids'])
            ->where('collection_status', 'pending')
            ->get();

        $confirmed = 0;
        foreach ($collections as $collection) {
            if (app(SettlementService::class)->confirmCourierCollection($collection)) {
                $confirmed++;
            }
        }

        return back()->with('success', "{$confirmed} collection(s) confirmed successfully.");
    }

    public function sync(Request $request)
    {
        $validated = $request->validate([
            'courier_id' => 'required|exists:couriers,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $courier = Courier::findOrFail($validated['courier_id']);

        if (!$courier->supports_payment_sync) {
            return back()->withErrors(['error' => "{$courier->name} does not support payment sync."]);
        }

        $syncService = app(CourierCollectionSyncService::class);
        $result = $syncService->syncFromCourier(
            $courier,
            $validated['date_from'],
            $validated['date_to'],
        );

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['error'] ?? 'Sync failed.']);
        }

        $message = "Sync completed: {$result['imported']} imported, {$result['reconciled']} auto-reconciled, {$result['skipped']} skipped.";
        if (!empty($result['errors'])) {
            $message .= ' ' . count($result['errors']) . ' errors.';
        }

        return back()->with('success', $message)->with('sync_result', $result);
    }

    public function syncSingle(Request $request, CourierCollection $collection)
    {
        if (!$collection->courier_id) {
            return back()->withErrors(['error' => 'No courier assigned to this collection.']);
        }

        $courier = $collection->courier;
        $invoice = $collection->consignment_id ?? $collection->reference_number;

        if (!$invoice) {
            return back()->withErrors(['error' => 'No invoice/consignment ID found.']);
        }

        $syncService = app(CourierCollectionSyncService::class);
        $result = $syncService->syncSingleOrder($courier, $invoice);

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['error'] ?? 'Single sync failed.']);
        }

        return back()->with('success', "Single sync completed: {$result['imported']} updated, {$result['reconciled']} reconciled.");
    }

    public function reconcile(Request $request, CourierCollection $collection)
    {
        $validated = $request->validate([
            'courier_charge' => 'nullable|numeric|min:0',
            'admin_notes' => 'nullable|string|max:1000',
            'discrepancy_notes' => 'nullable|string|max:1000',
        ]);

        $reconciliationService = app(ReconciliationService::class);
        $result = $reconciliationService->manualReconcile($collection, $validated, $request->user());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['error'] ?? 'Reconciliation failed.']);
        }

        return back()->with('success', 'Collection manually reconciled successfully.');
    }

    public function updateSettlementStatus(Request $request, CourierCollection $collection)
    {
        $validated = $request->validate([
            'settlement_status' => 'required|in:pending,settled,failed',
            'notes' => 'nullable|string|max:500',
        ]);

        $collection->update([
            'settlement_status' => $validated['settlement_status'],
            'notes' => $validated['notes'] ?? $collection->notes,
        ]);

        return back()->with('success', 'Settlement status updated successfully.');
    }
}
