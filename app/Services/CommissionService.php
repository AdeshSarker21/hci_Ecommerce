<?php

namespace App\Services;

use App\Models\CommissionRecord;
use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\Seller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionService
{
    private WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function calculateForOrder(Order $order): ?CommissionRecord
    {
        if ($order->payment_status !== 'paid') {
            return null;
        }

        $existing = CommissionRecord::where('order_id', $order->id)->first();
        if ($existing) {
            return $existing;
        }

        $rule = $this->findApplicableRule($order);
        $commissionAmount = $rule
            ? $rule->calculateCommission((float) $order->total)
            : $this->getDefaultCommission((float) $order->total);

        $sellerEarnings = round((float) $order->total - $commissionAmount, 2);

        return DB::transaction(function () use ($order, $rule, $commissionAmount, $sellerEarnings) {
            $record = CommissionRecord::create([
                'order_id' => $order->id,
                'seller_id' => $order->seller_id,
                'commission_rule_id' => $rule?->id,
                'order_amount' => $order->total,
                'commission_amount' => $commissionAmount,
                'seller_earnings' => $sellerEarnings,
                'commission_type' => $rule?->type ?? 'percentage',
                'commission_value' => $rule?->value ?? $this->getDefaultRate(),
                'status' => 'pending',
                'currency' => $order->currency,
            ]);

            $this->walletService->creditCommission($order->seller_id, $record);

            Log::info("Commission calculated for order #{$order->order_number}", [
                'order_id' => $order->id,
                'seller_id' => $order->seller_id,
                'order_amount' => $order->total,
                'commission' => $commissionAmount,
                'seller_earnings' => $sellerEarnings,
            ]);

            return $record;
        });
    }

    public function cancelForOrder(Order $order): bool
    {
        $record = CommissionRecord::where('order_id', $order->id)->where('status', 'pending')->first();
        if (!$record) {
            return false;
        }

        return DB::transaction(function () use ($record) {
            $record->update(['status' => 'cancelled']);

            $this->walletService->debitAdjustment(
                $record->seller_id,
                $record->seller_earnings,
                "Commission cancelled for order #{$record->order->order_number}",
                'CommissionRecord',
                $record->id
            );

            return true;
        });
    }

    private function findApplicableRule(Order $order): ?CommissionRule
    {
        $sellerId = $order->seller_id;

        // Check product-specific rules first
        $productIds = $order->items->pluck('product_id')->toArray();
        $productRule = CommissionRule::active()
            ->where('applies_to', 'product')
            ->whereIn('product_id', $productIds)
            ->orderByDesc('priority')
            ->first();
        if ($productRule) return $productRule;

        // Check category rules
        $categoryIds = \App\Models\Product::whereIn('id', $productIds)->pluck('category_id')->filter()->toArray();
        if (!empty($categoryIds)) {
            $categoryRule = CommissionRule::active()
                ->where('applies_to', 'category')
                ->whereIn('category_id', $categoryIds)
                ->orderByDesc('priority')
                ->first();
            if ($categoryRule) return $categoryRule;
        }

        // Check seller-specific rules
        $sellerRule = CommissionRule::active()
            ->where('applies_to', 'seller')
            ->where('seller_id', $sellerId)
            ->orderByDesc('priority')
            ->first();
        if ($sellerRule) return $sellerRule;

        // Fall back to global rules
        return CommissionRule::active()
            ->where('applies_to', 'global')
            ->orderByDesc('priority')
            ->first();
    }

    private function getDefaultCommission(float $amount): float
    {
        return round($amount * ($this->getDefaultRate() / 100), 2);
    }

    private function getDefaultRate(): float
    {
        return (float) (\App\Models\SystemSetting::where('key', 'default_commission_rate')->value('value') ?? '10');
    }
}
