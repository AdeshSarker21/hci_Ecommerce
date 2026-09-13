<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Courier;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Courier\CourierManager;
use App\Services\CommissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShipmentService
{
    public function __construct(
        private CourierManager $courierManager
    ) {}

    public function createShipmentForOrder(Order $order, ?int $courierId = null, ?string $note = null): array
    {
        $existing = Shipment::where('order_id', $order->id)
            ->whereNotIn('internal_status', ['cancelled', 'returned', 'delivery_failed'])
            ->first();

        if ($existing) {
            return [
                'success' => false,
                'error' => 'A shipment already exists for this order.',
                'shipment_id' => $existing->id,
            ];
        }

        $courier = $courierId
            ? Courier::findOrFail($courierId)
            : Courier::getDefault();

        if (!$courier || !$courier->is_active) {
            return ['success' => false, 'error' => 'No active courier configured.'];
        }

        if (!$courier->shipment_creation_support) {
            return ['success' => false, 'error' => "{$courier->name} does not support shipment creation via API."];
        }

        $shippingAddress = $order->shipping_address ?? [];

        $shipment = DB::transaction(function () use ($order, $courier, $shippingAddress, $note) {
            $items = $order->items()->with('product')->get();
            $totalWeight = $items->sum(fn ($item) => ($item->product?->weight ?? 0.5) * $item->quantity);
            $totalQuantity = $items->sum('quantity');

            $codFee = 0;
            if ($order->payment_method === 'cod' && $courier->cod_fee > 0) {
                $codFee = $courier->cod_fee;
            }

            $shipment = Shipment::create([
                'order_id' => $order->id,
                'courier_id' => $courier->id,
                'seller_id' => $order->seller_id,
                'status' => 'pending',
                'internal_status' => 'pending',
                'recipient_name' => $shippingAddress['name'] ?? $order->user?->name ?? '',
                'recipient_phone' => $shippingAddress['phone'] ?? $order->user?->phone ?? '',
                'recipient_address' => $shippingAddress['address'] ?? $shippingAddress['address_line_1'] ?? '',
                'recipient_city' => $shippingAddress['city'] ?? '',
                'recipient_area' => $shippingAddress['area'] ?? '',
                'recipient_zone' => $shippingAddress['zone'] ?? '',
                'cod_amount' => $order->payment_method === 'cod' ? $order->total : 0,
                'shipping_fee' => $order->shipping_cost,
                'cod_fee' => $codFee,
                'total_collectable' => ($order->payment_method === 'cod' ? $order->total : 0) + $codFee,
                'weight_kg' => $totalWeight > 0 ? $totalWeight : null,
                'quantity' => $totalQuantity,
                'courier_name' => $courier->slug,
                'delivery_type' => $order->payment_method === 'cod' ? 'cod' : 'prepaid',
                'pickup_address' => $order->seller?->business_address ?? '',
                'pickup_area' => $order->seller?->business_city ?? '',
                'note' => $note,
            ]);

            return $shipment;
        });

        try {
            $result = $this->courierManager->createShipment($shipment, $courier);

            if ($result['success']) {
                $updateData = [
                    'internal_status' => 'confirmed',
                    'status' => 'confirmed',
                    'consignment_id' => $result['consignment_id'] ?? null,
                    'tracking_code' => $result['tracking_code'] ?? $result['consignment_id'] ?? null,
                    'invoice_number' => $result['invoice_number'] ?? null,
                    'courier_response' => $result['data'] ?? $result,
                    'status_history' => [[
                        'from' => 'pending',
                        'to' => 'confirmed',
                        'details' => 'Shipment created with courier',
                        'timestamp' => now()->toISOString(),
                    ]],
                ];

                $shipment->update($updateData);

                if (!empty($result['consignment_id'])) {
                    $order->update([
                        'tracking_number' => $result['consignment_id'],
                        'courier_name' => $courier->slug,
                    ]);
                }

                ActivityLog::log(
                    'shipment_created',
                    "Shipment {$shipment->shipment_number} created for Order #{$order->order_number}",
                    $shipment,
                    [
                        'courier' => $courier->slug,
                        'consignment_id' => $result['consignment_id'] ?? null,
                        'order_id' => $order->id,
                    ]
                );

                return [
                    'success' => true,
                    'shipment' => $shipment->fresh(),
                    'consignment_id' => $result['consignment_id'] ?? null,
                ];
            }

            $shipment->update([
                'internal_status' => 'pending',
                'error_message' => $result['error'] ?? 'Shipment creation failed',
                'courier_response' => $result,
            ]);

            ActivityLog::log(
                'shipment_creation_failed',
                "Failed to create shipment for Order #{$order->order_number}: " . ($result['error'] ?? 'Unknown error'),
                $shipment,
                [
                    'courier' => $courier->slug,
                    'error' => $result['error'] ?? 'Unknown error',
                    'order_id' => $order->id,
                ]
            );

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Shipment creation failed with courier API.',
                'shipment_id' => $shipment->id,
            ];
        } catch (\Exception $e) {
            Log::error("Shipment creation exception for Order #{$order->order_number}", [
                'error' => $e->getMessage(),
                'courier' => $courier->slug,
            ]);

            $shipment->update([
                'internal_status' => 'pending',
                'error_message' => $e->getMessage(),
            ]);

            ActivityLog::log(
                'shipment_creation_failed',
                "Exception creating shipment for Order #{$order->order_number}: {$e->getMessage()}",
                $shipment,
                ['order_id' => $order->id, 'courier' => $courier->slug]
            );

            return [
                'success' => false,
                'error' => 'An error occurred while creating the shipment. Please retry.',
                'shipment_id' => $shipment->id,
            ];
        }
    }

    public function retryShipment(Shipment $shipment): array
    {
        if (!in_array($shipment->internal_status, ['pending', 'delivery_failed'])) {
            return ['success' => false, 'error' => 'Only pending or failed shipments can be retried.'];
        }

        if (!$shipment->courier) {
            return ['success' => false, 'error' => 'No courier assigned to this shipment.'];
        }

        if (!$shipment->courier->shipment_creation_support) {
            return ['success' => false, 'error' => "{$shipment->courier->name} does not support shipment creation."];
        }

        if ($shipment->consignment_id) {
            $existingCheck = Shipment::where('consignment_id', $shipment->consignment_id)
                ->where('id', '!=', $shipment->id)
                ->whereIn('internal_status', ['confirmed', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered'])
                ->first();

            if ($existingCheck) {
                return ['success' => false, 'error' => 'This consignment ID is already active in another shipment.'];
            }
        }

        $shipment->update([
            'error_message' => null,
            'courier_response' => null,
        ]);

        try {
            $result = $this->courierManager->createShipment($shipment, $shipment->courier);

            if ($result['success']) {
                $updateData = [
                    'internal_status' => 'confirmed',
                    'status' => 'confirmed',
                    'consignment_id' => $result['consignment_id'] ?? $shipment->consignment_id,
                    'tracking_code' => $result['tracking_code'] ?? $result['consignment_id'] ?? $shipment->tracking_code,
                    'invoice_number' => $result['invoice_number'] ?? $shipment->invoice_number,
                    'courier_response' => $result['data'] ?? $result,
                ];

                $history = $shipment->status_history ?? [];
                $history[] = [
                    'from' => 'pending',
                    'to' => 'confirmed',
                    'details' => 'Shipment creation retried successfully',
                    'timestamp' => now()->toISOString(),
                ];
                $updateData['status_history'] = $history;

                $shipment->update($updateData);

                if (!empty($result['consignment_id'])) {
                    $shipment->order->update([
                        'tracking_number' => $result['consignment_id'],
                        'courier_name' => $shipment->courier->slug,
                    ]);
                }

                ActivityLog::log(
                    'shipment_retried',
                    "Shipment {$shipment->shipment_number} retry successful",
                    $shipment,
                    ['consignment_id' => $result['consignment_id'] ?? null]
                );

                return ['success' => true, 'shipment' => $shipment->fresh()];
            }

            $shipment->update([
                'error_message' => $result['error'] ?? 'Retry failed',
                'courier_response' => $result,
            ]);

            ActivityLog::log(
                'shipment_creation_failed',
                "Retry failed for Shipment {$shipment->shipment_number}: " . ($result['error'] ?? 'Unknown'),
                $shipment,
                ['courier' => $shipment->courier->slug]
            );

            return ['success' => false, 'error' => $result['error'] ?? 'Retry failed.'];
        } catch (\Exception $e) {
            Log::error("Shipment retry exception", [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);

            $shipment->update(['error_message' => $e->getMessage()]);

            return ['success' => false, 'error' => 'An error occurred. Please retry.'];
        }
    }

    public function handleDelivery(Shipment $shipment): void
    {
        $order = $shipment->order;
        if (!$order) return;

        DB::transaction(function () use ($shipment, $order) {
            if ($order->status !== 'delivered') {
                $order->update([
                    'status' => 'delivered',
                    'delivered_at' => $shipment->delivered_at ?? now(),
                ]);
            }

            if ($order->payment_method === 'cod' && $order->payment_status !== 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'paid_at' => $order->paid_at ?? now(),
                ]);
            }

            if ($order->payment_method === 'cod' && $order->seller_payment_status !== 'collected_by_courier') {
                $order->update(['seller_payment_status' => 'collected_by_courier']);

                $commissionRecord = $order->commissionRecord;

                \App\Models\CourierCollection::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'seller_id' => $order->seller_id,
                        'commission_record_id' => $commissionRecord?->id,
                        'courier_name' => $shipment->courier?->slug ?? $shipment->courier_name,
                        'consignment_id' => $shipment->consignment_id,
                        'cod_amount' => (float) $shipment->cod_amount,
                        'commission_amount' => $commissionRecord?->commission_amount ?? 0,
                        'seller_earning' => $commissionRecord?->seller_earnings ?? 0,
                        'collection_status' => 'pending',
                        'settlement_status' => 'pending',
                        'collected_at' => now(),
                        'delivery_date' => $shipment->delivered_at ?? now(),
                    ]
                );
            }

            if ($order->payment_status === 'paid' && !$order->commissionRecord) {
                try {
                    app(CommissionService::class)->calculateForOrder($order);
                } catch (\Exception $e) {
                    Log::error("Failed to calculate commission on delivery", [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        ActivityLog::log(
            'delivery_confirmed',
            "Delivery confirmed for Shipment {$shipment->shipment_number}",
            $shipment,
            ['order_id' => $order->id, 'delivered_at' => $shipment->delivered_at?->toISOString()]
        );
    }

    public function handleReturn(Shipment $shipment): void
    {
        $order = $shipment->order;
        if (!$order) return;

        DB::transaction(function () use ($shipment, $order) {
            if ($order->payment_method === 'cod' && $order->payment_status !== 'refunded') {
                $order->update(['payment_status' => 'pending']);
            }

            if (in_array($order->seller_payment_status, ['collected_by_courier', 'pending_collection'])) {
                $order->update(['seller_payment_status' => 'pending_collection']);
            }
        });

        ActivityLog::log(
            'return_requested',
            "Return processed for Shipment {$shipment->shipment_number}",
            $shipment,
            ['order_id' => $order->id]
        );
    }

    public function handleFailedDelivery(Shipment $shipment): void
    {
        $order = $shipment->order;
        if (!$order) return;

        if (in_array($order->seller_payment_status, ['collected_by_courier'])) {
            $order->update(['seller_payment_status' => 'pending_collection']);
        }

        ActivityLog::log(
            'delivery_failed',
            "Delivery failed for Shipment {$shipment->shipment_number}",
            $shipment,
            ['order_id' => $order->id]
        );
    }
}
