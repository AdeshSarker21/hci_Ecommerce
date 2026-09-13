<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;

class SellerShipmentController extends Controller
{
    public function index(Request $request)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $query = Shipment::with([
            'order:id,order_number,total,payment_method',
            'courier:id,name,slug,logo',
        ])
        ->where('seller_id', $seller->id);

        if ($status = $request->input('status')) {
            $query->where('internal_status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('consignment_id', 'like', "%{$search}%")
                  ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"));
            });
        }

        $shipments = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => Shipment::where('seller_id', $seller->id)->count(),
            'pending' => Shipment::where('seller_id', $seller->id)->where('internal_status', 'pending')->count(),
            'in_transit' => Shipment::where('seller_id', $seller->id)->where('internal_status', 'in_transit')->count(),
            'delivered' => Shipment::where('seller_id', $seller->id)->where('internal_status', 'delivered')->count(),
            'returned' => Shipment::where('seller_id', $seller->id)->where('internal_status', 'returned')->count(),
        ];

        return view('seller.shipments.index', compact('shipments', 'stats', 'seller'));
    }

    public function show(Shipment $shipment)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $shipment->seller_id !== $seller->id) {
            abort(403);
        }

        $shipment->load([
            'order' => fn ($q) => $q->with(['user:id,name,email,phone', 'items.product:id,name,sku']),
            'courier:id,name,slug,logo',
        ]);

        return view('seller.shipments.show', compact('shipment', 'seller'));
    }
}
