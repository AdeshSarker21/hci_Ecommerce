<?php

namespace App\Services;

use App\Models\CommissionRecord;
use App\Models\CourierCollection;
use App\Models\Order;
use App\Models\Settlement;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettlementService
{
    private WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function confirmCourierCollection(CourierCollection $collection): bool
    {
        if ($collection->collection_status !== 'pending') {
            return false;
        }

        return DB::transaction(function () use ($collection) {
            $collection->update([
                'collection_status' => 'confirmed',
                'confirmed_at' => now(),
                'settlement_status' => 'pending',
            ]);

            $collection->order->update([
                'seller_payment_status' => 'awaiting_settlement',
            ]);

            Log::info("Courier collection confirmed for order #{$collection->order->order_number}", [
                'collection_id' => $collection->id,
                'seller_id' => $collection->seller_id,
            ]);

            return true;
        });
    }

    public function confirmAllPendingCollections(int $sellerId): int
    {
        $collections = CourierCollection::where('seller_id', $sellerId)
            ->where('collection_status', 'pending')
            ->get();

        $confirmed = 0;
        foreach ($collections as $collection) {
            if ($this->confirmCourierCollection($collection)) {
                $confirmed++;
            }
        }

        return $confirmed;
    }

    public function createSettlement(int $sellerId, float $amount, ?string $notes = null): ?Settlement
    {
        $wallet = $this->walletService->getOrCreate($sellerId);

        if ((float) $wallet->pending_balance < $amount || $amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($sellerId, $wallet, $amount, $notes) {
            $settlement = Settlement::create([
                'seller_id' => $sellerId,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'status' => 'pending',
                'notes' => $notes,
            ]);

            $this->walletService->settlePending($sellerId, $amount, "Settlement #{$settlement->settlement_number}");

            $pendingRecords = CommissionRecord::where('seller_id', $sellerId)
                ->where('status', 'pending')
                ->orderBy('created_at')
                ->get();

            $remaining = $amount;
            $settledOrderIds = [];
            foreach ($pendingRecords as $record) {
                if ($remaining <= 0) break;
                $record->update(['status' => 'settled', 'settled_at' => now()]);
                $remaining -= (float) $record->seller_earnings;
                $settledOrderIds[] = $record->order_id;

                CourierCollection::where('order_id', $record->order_id)
                    ->where('seller_id', $sellerId)
                    ->update(['settlement_status' => 'settled']);
            }

            if (!empty($settledOrderIds)) {
                Order::whereIn('id', $settledOrderIds)
                    ->where('seller_payment_status', 'awaiting_settlement')
                    ->update(['seller_payment_status' => 'available_for_payout']);
            }

            Log::info("Settlement created for seller #{$sellerId}", [
                'settlement_id' => $settlement->id,
                'amount' => $amount,
                'orders_settled' => count($settledOrderIds),
            ]);

            return $settlement;
        });
    }

    public function startPayout(Settlement $settlement): bool
    {
        if ($settlement->status !== 'pending' && $settlement->status !== 'processing') {
            return false;
        }

        return DB::transaction(function () use ($settlement) {
            $settlement->update(['status' => 'processing']);

            $settlementOrders = $this->getSettlementOrders($settlement);
            $settlementOrders->each(function ($order) {
                if ($order->seller_payment_status === 'available_for_payout') {
                    $order->update(['seller_payment_status' => 'payout_processing']);
                }
            });

            Log::info("Settlement payout started #{$settlement->settlement_number}");
            return true;
        });
    }

    public function completeSettlement(Settlement $settlement): bool
    {
        if ($settlement->status !== 'pending' && $settlement->status !== 'processing') {
            return false;
        }

        return DB::transaction(function () use ($settlement) {
            $result = $this->walletService->processWithdrawal($settlement->seller_id, (float) $settlement->amount);

            if (!$result) {
                return false;
            }

            $settlement->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $settlementOrders = $this->getSettlementOrders($settlement);
            $settlementOrders->each(function ($order) {
                $order->update(['seller_payment_status' => 'paid']);
            });

            Log::info("Settlement #{$settlement->settlement_number} completed");
            return true;
        });
    }

    public function failSettlement(Settlement $settlement, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($settlement, $reason) {
            $settlement->update([
                'status' => 'failed',
                'notes' => ($settlement->notes ? $settlement->notes . "\n" : '') . 'Failed: ' . ($reason ?? 'Unknown'),
            ]);

            $this->walletService->getOrCreate($settlement->seller_id);
            $wallet = Wallet::where('seller_id', $settlement->seller_id)->first();
            if ($wallet) {
                $wallet->increment('pending_balance', $settlement->amount);
                $wallet->decrement('available_balance', $settlement->amount);

                $wallet->transactions()->create([
                    'seller_id' => $settlement->seller_id,
                    'type' => 'adjustment_credit',
                    'amount' => $settlement->amount,
                    'balance_before' => (float) $wallet->available_balance,
                    'balance_after' => (float) $wallet->available_balance + (float) $settlement->amount,
                    'reference_type' => Settlement::class,
                    'reference_id' => $settlement->id,
                    'description' => "Settlement #{$settlement->settlement_number} failed - funds returned to pending",
                ]);
            }

            $settlementOrders = $this->getSettlementOrders($settlement);
            $settlementOrders->each(function ($order) {
                $order->update(['seller_payment_status' => 'awaiting_settlement']);
            });

            return true;
        });
    }

    public function settleAllPending(int $sellerId): ?Settlement
    {
        $wallet = $this->walletService->getOrCreate($sellerId);
        $pendingBalance = (float) $wallet->pending_balance;

        if ($pendingBalance <= 0) {
            return null;
        }

        return $this->createSettlement($sellerId, $pendingBalance, 'Auto-settlement of all pending earnings');
    }

    private function getSettlementOrders(Settlement $settlement): \Illuminate\Database\Eloquent\Collection
    {
        return Order::where('seller_id', $settlement->seller_id)
            ->whereHas('commissionRecord', function ($q) {
                $q->where('status', 'settled');
            })
            ->whereIn('seller_payment_status', ['available_for_payout', 'payout_processing'])
            ->get();
    }
}
