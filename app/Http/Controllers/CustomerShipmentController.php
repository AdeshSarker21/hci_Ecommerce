<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\Request;

class CustomerShipmentController extends Controller
{
    public function track(Request $request, Order $order)
    {
        $user = auth()->user();

        if ($order->user_id !== $user->id) {
            abort(403);
        }

        $order->load([
            'items.product:id,name,sku,price',
            'seller:id,store_name',
        ]);

        $shipment = Shipment::with([
            'courier:id,name,slug,logo',
        ])
        ->where('order_id', $order->id)
        ->latest()
        ->first();

        return view('account.track-order', compact('order', 'shipment'));
    }
}
