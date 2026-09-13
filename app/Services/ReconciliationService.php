<?php

namespace App\Services;

use App\Models\Courier;
use App\Models\CourierCollection;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReconciliationService
{
    public function reconcileCollection(CourierCollection $collection, array $courierData, ?User $reconciledBy = null): array
    {
        $order = $collection->order;
        $shipment = $collection->shipment;
        $discrepancies = [];

        $declaredCod = (float) ($courierData['cod_amount'] ?? $collection->cod_amount);
        $courierCharge = (float) ($courierData['courier_charge'] ?? 0);
        $netCollected = (float) ($courierData['net_collected'] ?? ($declaredCod - $courierCharge));

        if (abs($declaredCod - $collection->cod_amount) > 0.01) {
            $discrepancies[] = "COD amount mismatch: system={$collection->cod_amount}, courier={$declaredCod}";
        }

        if ($order && $order->payment_method === 'cod') {
            $expectedCod = (float) $order->total_amount;
            if (abs($expectedCod - $declaredCod) > 0.01) {
                $discrepancies[] = "Order total mismatch: order={$expectedCod}, courier={$declaredCod}";
            }
        }

        if ($shipment && $shipment->cod_amount > 0) {
            if (abs($shipment->cod_amount - $declaredCod) > 0.01) {
                $discrepancies[] = "Shipment COD mismatch: shipment={$shipment->cod_amount}, courier={$declaredCod}";
            }
        }

        $reconciliationStatus = empty($discrepancies) ? 'reconciled' : 'discrepancy';

        DB::beginTransaction();

        try {
            $collection->update([
                'courier_charge' => $courierCharge,
                'net_collected' => $netCollected,
                'courier_payment_reference' => $courierData['payment_reference'] ?? null,
                'courier_payment_date' => $courierData['payment_date'] ?? null,
                'discrepancy_amount' => empty($discrepancies) ? null : abs($declaredCod - $collection->cod_amount),
                'discrepancy_notes' => empty($discrepancies) ? null : implode('; ', $discrepancies),
                'reconciliation_status' => $reconciliationStatus,
                'reconciliation_reference' => $reconciliationStatus === 'reconciled' ? 'auto-' . now()->timestamp : null,
                'reconciled_at' => $reconciliationStatus === 'reconciled' ? now() : null,
                'reconciled_by' => $reconciledBy?->id,
                'raw_courier_data' => $courierData,
            ]);

            if ($reconciliationStatus === 'reconciled' && $collection->collection_status !== 'confirmed') {
                $collection->update([
                    'collection_status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'status' => $reconciliationStatus,
                'discrepancies' => $discrepancies,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reconciliation failed', [
                'collection_id' => $collection->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function manualReconcile(CourierCollection $collection, array $data, User $reconciledBy): array
    {
        DB::beginTransaction();

        try {
            $updateData = [
                'reconciliation_status' => 'reconciled',
                'reconciliation_reference' => 'manual-' . now()->timestamp,
                'reconciled_at' => now(),
                'reconciled_by' => $reconciledBy->id,
                'admin_notes' => $data['admin_notes'] ?? null,
            ];

            if (isset($data['courier_charge'])) {
                $updateData['courier_charge'] = $data['courier_charge'];
                $updateData['net_collected'] = $collection->cod_amount - $data['courier_charge'];
            }

            if (isset($data['discrepancy_amount'])) {
                $updateData['discrepancy_amount'] = $data['discrepancy_amount'];
            }

            if (isset($data['discrepancy_notes'])) {
                $updateData['discrepancy_notes'] = $data['discrepancy_notes'];
            }

            $collection->update($updateData);

            if ($collection->collection_status !== 'confirmed') {
                $collection->update([
                    'collection_status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'status' => 'reconciled',
                'message' => 'Collection manually reconciled by ' . $reconciledBy->name,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Manual reconciliation failed', [
                'collection_id' => $collection->id,
                'user_id' => $reconciledBy->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function importFromCourier(Courier $courier, array $collections, ?string $batchId = null): array
    {
        $batchId = $batchId ?? 'batch-' . now()->timestamp;
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $reconciled = 0;

        DB::beginTransaction();

        try {
            foreach ($collections as $item) {
                $invoice = $item['invoice'] ?? null;
                if (!$invoice) {
                    $errors[] = 'Missing invoice number for item';
                    continue;
                }

                $existing = CourierCollection::where('consignment_id', $invoice)
                    ->orWhere('reference_number', $invoice)
                    ->first();

                if ($existing) {
                    if ($existing->reconciliation_status === 'pending') {
                        $result = $this->reconcileCollection($existing, $item);
                        if ($result['success'] && $result['status'] === 'reconciled') {
                            $reconciled++;
                        }
                    } else {
                        $skipped++;
                    }
                    continue;
                }

                $shipment = Shipment::where('consignment_id', $invoice)->first();
                $order = $shipment?->order;

                $collection = CourierCollection::create([
                    'order_id' => $order?->id,
                    'seller_id' => $order->seller_id ?? $shipment?->seller_id,
                    'courier_id' => $courier->id,
                    'courier_name' => $courier->name,
                    'consignment_id' => $invoice,
                    'cod_amount' => $item['cod_amount'] ?? 0,
                    'courier_charge' => $item['courier_charge'] ?? 0,
                    'net_collected' => $item['net_collected'] ?? ($item['cod_amount'] ?? 0) - ($item['courier_charge'] ?? 0),
                    'marketplace_earning' => 0,
                    'commission_amount' => 0,
                    'seller_earning' => 0,
                    'collection_status' => 'pending',
                    'settlement_status' => 'pending',
                    'delivery_date' => $item['payment_date'] ?? now(),
                    'reference_number' => $invoice,
                    'import_batch_id' => $batchId,
                    'raw_courier_data' => $item['raw'] ?? $item,
                ]);

                $reconcileResult = $this->reconcileCollection($collection, $item);
                if ($reconcileResult['success'] && $reconcileResult['status'] === 'reconciled') {
                    $reconciled++;
                }

                $imported++;
            }

            DB::commit();

            return [
                'success' => true,
                'batch_id' => $batchId,
                'imported' => $imported,
                'reconciled' => $reconciled,
                'skipped' => $skipped,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import from courier failed', [
                'courier_id' => $courier->id,
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getUnmatchedStats(): array
    {
        $total = CourierCollection::count();
        $pending = CourierCollection::where('reconciliation_status', 'pending')->count();
        $reconciled = CourierCollection::where('reconciliation_status', 'reconciled')->count();
        $discrepancy = CourierCollection::where('reconciliation_status', 'discrepancy')->count();
        $manualReview = CourierCollection::where('reconciliation_status', 'manual_review')->count();

        $totalCod = CourierCollection::sum('cod_amount');
        $totalNet = CourierCollection::sum('net_collected');
        $totalDiscrepancy = CourierCollection::whereNotNull('discrepancy_amount')->sum('discrepancy_amount');

        return [
            'total' => $total,
            'pending' => $pending,
            'reconciled' => $reconciled,
            'discrepancy' => $discrepancy,
            'manual_review' => $manualReview,
            'total_cod_amount' => $totalCod,
            'total_net_collected' => $totalNet,
            'total_discrepancy' => $totalDiscrepancy,
        ];
    }
}
