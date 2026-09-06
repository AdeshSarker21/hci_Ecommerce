<?php

namespace App\Services;

use App\Models\CommissionRecord;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    public function getOrCreate(int $sellerId): Wallet
    {
        return Wallet::firstOrCreate(
            ['seller_id' => $sellerId],
            [
                'pending_balance' => 0,
                'available_balance' => 0,
                'withdrawn_amount' => 0,
                'total_earned' => 0,
            ]
        );
    }

    public function creditCommission(int $sellerId, CommissionRecord $record): WalletTransaction
    {
        $wallet = $this->getOrCreate($sellerId);

        return DB::transaction(function () use ($wallet, $record) {
            $balanceBefore = (float) $wallet->pending_balance;
            $amount = (float) $record->seller_earnings;
            $balanceAfter = $balanceBefore + $amount;

            $wallet->increment('pending_balance', $amount);
            $wallet->increment('total_earned', $amount);

            return $wallet->transactions()->create([
                'seller_id' => $wallet->seller_id,
                'type' => 'commission_credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => CommissionRecord::class,
                'reference_id' => $record->id,
                'description' => "Commission earned from order #{$record->order->order_number}",
                'metadata' => [
                    'order_id' => $record->order_id,
                    'order_number' => $record->order->order_number,
                    'commission_amount' => $record->commission_amount,
                ],
            ]);
        });
    }

    public function settlePending(int $sellerId, float $amount, string $notes = null): bool
    {
        $wallet = $this->getOrCreate($sellerId);

        if ((float) $wallet->pending_balance < $amount) {
            return false;
        }

        return DB::transaction(function () use ($wallet, $amount, $notes) {
            $pendingBefore = (float) $wallet->pending_balance;
            $availableBefore = (float) $wallet->available_balance;

            $wallet->decrement('pending_balance', $amount);
            $wallet->increment('available_balance', $amount);

            $wallet->transactions()->create([
                'seller_id' => $wallet->seller_id,
                'type' => 'settlement_credit',
                'amount' => $amount,
                'balance_before' => $availableBefore,
                'balance_after' => $availableBefore + $amount,
                'description' => $notes ?? 'Pending balance settled to available',
            ]);

            return true;
        });
    }

    public function processWithdrawal(int $sellerId, float $amount): bool
    {
        $wallet = $this->getOrCreate($sellerId);

        if ((float) $wallet->available_balance < $amount || $amount <= 0) {
            return false;
        }

        return DB::transaction(function () use ($wallet, $amount) {
            $availableBefore = (float) $wallet->available_balance;
            $availableAfter = $availableBefore - $amount;

            $wallet->decrement('available_balance', $amount);
            $wallet->increment('withdrawn_amount', $amount);

            $wallet->transactions()->create([
                'seller_id' => $wallet->seller_id,
                'type' => 'withdrawal_debit',
                'amount' => $amount,
                'balance_before' => $availableBefore,
                'balance_after' => $availableAfter,
                'description' => 'Withdrawal processed',
            ]);

            return true;
        });
    }

    public function debitAdjustment(int $sellerId, float $amount, string $description, ?string $referenceType = null, ?int $referenceId = null): WalletTransaction
    {
        $wallet = $this->getOrCreate($sellerId);

        return DB::transaction(function () use ($wallet, $amount, $description, $referenceType, $referenceId) {
            $pendingBefore = (float) $wallet->pending_balance;

            // Check if we can debit from pending, otherwise from available
            if ($pendingBefore >= $amount) {
                $wallet->decrement('pending_balance', $amount);
                $balanceAfter = (float) $wallet->pending_balance;
                $type = 'adjustment_debit';
            } else {
                $availableBefore = (float) $wallet->available_balance;
                $wallet->decrement('available_balance', $amount);
                $balanceAfter = (float) $wallet->available_balance;
                $type = 'adjustment_debit';
            }

            $wallet->decrement('total_earned', $amount);

            return $wallet->transactions()->create([
                'seller_id' => $wallet->seller_id,
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $pendingBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);
        });
    }

    public function getBalance(int $sellerId): array
    {
        $wallet = $this->getOrCreate($sellerId);

        return [
            'pending' => (float) $wallet->pending_balance,
            'available' => (float) $wallet->available_balance,
            'withdrawn' => (float) $wallet->withdrawn_amount,
            'total_earned' => (float) $wallet->total_earned,
            'total_balance' => (float) $wallet->pending_balance + (float) $wallet->available_balance,
        ];
    }
}
