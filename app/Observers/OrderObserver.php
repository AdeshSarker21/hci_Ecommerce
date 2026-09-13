<?php

namespace App\Observers;

use App\Models\CommissionRecord;
use App\Models\CourierCollection;
use App\Models\Order;
use App\Services\CommissionService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (!$order->wasChanged('status')) {
            return;
        }

        $oldStatus = $order->getOriginal('status');
        $newStatus = $order->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        Log::info("OrderObserver: status changed from {$oldStatus} to {$newStatus}", [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'seller_id' => $order->seller_id,
        ]);

        if ($newStatus === 'delivered') {
            $this->handleDelivered($order);
        } elseif ($newStatus === 'cancelled') {
            $this->handleCancelled($order);
        }
    }

    private function handleDelivered(Order $order): void
    {
        if ($order->payment_method === 'cod' && $order->payment_status !== 'paid') {
            $order->update(['payment_status' => 'paid', 'paid_at' => $order->paid_at ?? now()]);
            Log::info("OrderObserver: COD order marked as paid on delivery", ['order_id' => $order->id]);
        }

        if ($order->payment_method === 'cod' && $order->seller_payment_status !== 'collected_by_courier') {
            $order->update(['seller_payment_status' => 'collected_by_courier']);

            $commissionRecord = CommissionRecord::where('order_id', $order->id)->first();

            CourierCollection::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'seller_id' => $order->seller_id,
                    'commission_record_id' => $commissionRecord?->id,
                    'courier_name' => $order->courier_name ?? 'steadfast',
                    'consignment_id' => $order->tracking_number,
                    'cod_amount' => (float) $order->total,
                    'commission_amount' => $commissionRecord?->commission_amount ?? 0,
                    'seller_earning' => $commissionRecord?->seller_earnings ?? 0,
                    'collection_status' => 'pending',
                    'settlement_status' => 'pending',
                    'collected_at' => now(),
                    'delivery_date' => $order->delivered_at ?? now(),
                ]
            );

            Log::info("OrderObserver: COD courier collection created", ['order_id' => $order->id]);
        }

        if ($order->payment_status !== 'paid') {
            Log::info("OrderObserver: skipping commission - order not paid", ['order_id' => $order->id]);
            return;
        }

        $existing = CommissionRecord::where('order_id', $order->id)->where('status', '!=', 'cancelled')->first();
        if ($existing) {
            Log::info("OrderObserver: commission already exists", ['order_id' => $order->id]);
            return;
        }

        try {
            app(CommissionService::class)->calculateForOrder($order);
            Log::info("OrderObserver: commission auto-calculated for delivered order", ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error("OrderObserver: failed to calculate commission", [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function handleCancelled(Order $order): void
    {
        $existing = CommissionRecord::where('order_id', $order->id)->where('status', 'pending')->first();
        if (!$existing) {
            return;
        }

        try {
            app(CommissionService::class)->cancelForOrder($order);
            Log::info("OrderObserver: commission auto-cancelled for cancelled order", ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error("OrderObserver: failed to cancel commission", [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
