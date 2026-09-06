<?php

namespace App\Services;

use App\Models\CommissionRecord;
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

            // Mark related commission records as settled
            $pendingRecords = CommissionRecord::where('seller_id', $sellerId)
                ->where('status', 'pending')
                ->orderBy('created_at')
                ->get();

            $remaining = $amount;
            foreach ($pendingRecords as $record) {
                if ($remaining <= 0) break;
                $record->update(['status' => 'settled', 'settled_at' => now()]);
                $remaining -= (float) $record->seller_earnings;
            }

            Log::info("Settlement created for seller #{$sellerId}", [
                'settlement_id' => $settlement->id,
                'amount' => $amount,
            ]);

            return $settlement;
        });
    }

    public function completeSettlement(Settlement $settlement): bool
    {
        if ($settlement->status !== 'pending' && $settlement->status !== 'processing') {
            return false;
        }

        return DB::transaction(function () use ($settlement) {
            $settlement->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

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

            // Return funds to pending
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
}
