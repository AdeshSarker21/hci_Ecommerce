<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class FixCodDeliveredPaymentStatus extends Command
{
    protected $signature = 'orders:fix-cod-payment';
    protected $description = 'Mark delivered COD orders as paid';

    public function handle(): int
    {
        $orders = Order::where('payment_method', 'cod')
            ->where('status', 'delivered')
            ->where('payment_status', '!=', 'paid')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No delivered COD orders with pending payment found.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();

        foreach ($orders as $order) {
            $order->update([
                'payment_status' => 'paid',
                'paid_at' => $order->delivered_at ?? now(),
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Updated {$orders->count()} COD order(s) to paid.");

        return self::SUCCESS;
    }
}
