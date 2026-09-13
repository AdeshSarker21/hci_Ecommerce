<?php

namespace App\Console\Commands;

use App\Models\CommissionRecord;
use App\Models\CourierCollection;
use App\Models\Order;
use Illuminate\Console\Command;

class FixSellerPaymentStatus extends Command
{
    protected $signature = 'orders:fix-seller-payment-status';
    protected $description = 'Fix seller_payment_status for existing delivered COD orders';

    public function handle(): int
    {
        $deliveredCodOrders = Order::where('status', 'delivered')
            ->where('payment_method', 'cod')
            ->where('payment_status', 'paid')
            ->whereNull('seller_payment_status')
            ->get();

        if ($deliveredCodOrders->isEmpty()) {
            $this->info('No delivered COD orders need fixing.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($deliveredCodOrders->count());
        $bar->start();

        foreach ($deliveredCodOrders as $order) {
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
                    'collection_status' => 'confirmed',
                    'settlement_status' => 'pending',
                    'collected_at' => $order->delivered_at,
                    'confirmed_at' => $order->delivered_at,
                    'delivery_date' => $order->delivered_at,
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Fixed {$deliveredCodOrders->count()} delivered COD order(s) with seller_payment_status and courier collections.");

        $deliveredOnlineOrders = Order::where('status', 'delivered')
            ->where('payment_method', '!=', 'cod')
            ->where('payment_status', 'paid')
            ->whereNull('seller_payment_status')
            ->count();

        if ($deliveredOnlineOrders > 0) {
            $this->info("Note: {$deliveredOnlineOrders} delivered online orders have no seller_payment_status (not applicable for COD lifecycle).");
        }

        return self::SUCCESS;
    }
}
