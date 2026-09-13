<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Courier;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Shipment;
use App\Services\Courier\CourierManager;
use App\Services\ShipmentService;
use Illuminate\Http\Request;

class AdminShipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Shipment::with([
            'order:id,order_number,user_id,seller_id,total,payment_method,payment_status,seller_payment_status',
            'courier:id,name,slug,logo',
            'seller:id,store_name',
            'order.user:id,name,email',
        ]);

        if ($status = $request->input('status')) {
            $query->where('internal_status', $status);
        }

        if ($courierId = $request->input('courier_id')) {
            $query->where('courier_id', $courierId);
        }

        if ($sellerId = $request->input('seller_id')) {
            $query->where('seller_id', $sellerId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('consignment_id', 'like', "%{$search}%")
                  ->orWhere('tracking_code', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"));
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $shipments = $query->latest()->paginate(20)->withQueryString();
        $couriers = Courier::active()->orderBy('name')->get();
        $sellers = Seller::approved()->orderBy('store_name')->get();

        $stats = [
            'total' => Shipment::count(),
            'pending' => Shipment::where('internal_status', 'pending')->count(),
            'confirmed' => Shipment::where('internal_status', 'confirmed')->count(),
            'in_transit' => Shipment::where('internal_status', 'in_transit')->count(),
            'delivered' => Shipment::where('internal_status', 'delivered')->count(),
            'returned' => Shipment::where('internal_status', 'returned')->count(),
            'failed' => Shipment::where('internal_status', 'delivery_failed')->count(),
        ];

        return view('admin.shipments.index', compact('shipments', 'couriers', 'sellers', 'stats'));
    }

    public function show(Shipment $shipment)
    {
        $shipment->load([
            'order' => fn ($q) => $q->with(['user:id,name,email,phone', 'items.product:id,name,sku,price', 'commissionRecord', 'courierCollection']),
            'courier:id,name,slug,logo,api_base_url',
            'seller:id,store_name,contact_phone,business_address',
            'webhookLogs' => fn ($q) => $q->latest()->limit(15),
        ]);

        $activities = ActivityLog::where('subject_type', Shipment::class)
            ->where('subject_id', $shipment->id)
            ->with('user:id,name')
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.shipments.show', compact('shipment', 'activities'));
    }

    public function create(Order $order)
    {
        if ($order->shipment()->exists()) {
            return redirect()->route('admin.shipments.show', $order->shipment)
                ->with('info', 'This order already has a shipment.');
        }

        $couriers = Courier::active()->where('shipment_creation_support', true)->orderBy('name')->get();

        return view('admin.shipments.create', compact('order', 'couriers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'courier_id' => 'required|exists:couriers,id',
            'note' => 'nullable|string|max:500',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        $shipmentService = app(ShipmentService::class);
        $result = $shipmentService->createShipmentForOrder(
            $order,
            $validated['courier_id'],
            $validated['note'] ?? null
        );

        if ($result['success']) {
            return redirect()->route('admin.shipments.show', $result['shipment'])
                ->with('success', "Shipment created successfully. Consignment: " . ($result['consignment_id'] ?? 'Pending'));
        }

        return back()->withErrors(['error' => $result['error']])->withInput();
    }

    public function retry(Shipment $shipment)
    {
        $shipmentService = app(ShipmentService::class);
        $result = $shipmentService->retryShipment($shipment);

        if ($result['success']) {
            return back()->with('success', 'Shipment retry successful. Consignment: ' . ($result['shipment']?->consignment_id ?? 'Updated'));
        }

        return back()->withErrors(['error' => $result['error']]);
    }

    public function syncStatus(Shipment $shipment)
    {
        try {
            $manager = app(CourierManager::class);
            $result = $manager->syncStatus($shipment);

            if ($result['success']) {
                ActivityLog::log(
                    'status_synchronized',
                    "Status synced for Shipment {$shipment->shipment_number}",
                    $shipment,
                    ['new_status' => $shipment->fresh()->internal_status]
                );

                return back()->with('success', "Shipment status synced: {$shipment->fresh()->status_label}");
            }

            return back()->withErrors(['error' => "Sync failed: " . ($result['error'] ?? 'Unknown error')]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => "Sync failed: {$e->getMessage()}"]);
        }
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Shipment::statuses())),
            'note' => 'nullable|string|max:500',
        ]);

        $oldStatus = $shipment->internal_status;
        $shipment->updateStatus($validated['status'], $validated['note']);

        ActivityLog::log(
            'admin_status_update',
            "Admin manually changed Shipment {$shipment->shipment_number} from {$oldStatus} to {$validated['status']}",
            $shipment,
            ['old_status' => $oldStatus, 'new_status' => $validated['status'], 'note' => $validated['note'] ?? null]
        );

        if ($validated['status'] === 'delivered') {
            app(ShipmentService::class)->handleDelivery($shipment);
        } elseif ($validated['status'] === 'returned') {
            app(ShipmentService::class)->handleReturn($shipment);
        } elseif ($validated['status'] === 'delivery_failed') {
            app(ShipmentService::class)->handleFailedDelivery($shipment);
        }

        return back()->with('success', "Shipment status updated to {$shipment->fresh()->status_label}.");
    }

    public function deliveryStatus(Request $request)
    {
        $query = Shipment::with([
            'order:id,order_number',
            'courier:id,name,slug',
            'seller:id,store_name',
            'order.user:id,name',
        ]);

        if ($status = $request->input('status')) {
            $query->where('internal_status', $status);
        }

        if ($courierId = $request->input('courier_id')) {
            $query->where('courier_id', $courierId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('consignment_id', 'like', "%{$search}%")
                  ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"));
            });
        }

        $shipments = $query->latest()->paginate(20)->withQueryString();
        $couriers = Courier::active()->orderBy('name')->get();

        $stats = [
            'total' => Shipment::count(),
            'pending' => Shipment::pending()->count(),
            'picked_up' => Shipment::where('internal_status', 'picked_up')->count(),
            'in_transit' => Shipment::where('internal_status', 'in_transit')->count(),
            'out_for_delivery' => Shipment::where('internal_status', 'out_for_delivery')->count(),
            'delivered' => Shipment::where('internal_status', 'delivered')->count(),
            'returned' => Shipment::where('internal_status', 'returned')->count(),
        ];

        return view('admin.shipments.delivery-status', compact('shipments', 'couriers', 'stats'));
    }

    public function tracking(Request $request)
    {
        $shipment = null;
        $searched = false;

        if ($query = $request->input('q')) {
            $searched = true;
            $shipment = Shipment::with(['order:id,order_number', 'courier:id,name,slug', 'seller:id,store_name'])
                ->where('shipment_number', $query)
                ->orWhere('consignment_id', $query)
                ->orWhere('tracking_code', $query)
                ->orWhereHas('order', fn ($oq) => $oq->where('order_number', $query))
                ->first();
        }

        return view('admin.shipments.tracking', compact('shipment', 'searched'));
    }
}
