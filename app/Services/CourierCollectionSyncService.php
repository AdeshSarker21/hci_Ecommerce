<?php

namespace App\Services;

use App\Models\Courier;
use App\Models\CourierCollection;
use App\Services\Courier\CourierManager;
use Illuminate\Support\Facades\Log;

class CourierCollectionSyncService
{
    public function __construct(
        private CourierManager $courierManager,
        private ReconciliationService $reconciliationService,
    ) {}

    public function syncFromCourier(Courier $courier, string $dateFrom, string $dateTo): array
    {
        if (!$courier->supports_payment_sync) {
            return [
                'success' => false,
                'error' => "Payment sync is not supported by {$courier->name}",
            ];
        }

        $courierService = $this->courierManager->get($courier->slug);

        if (!$courierService) {
            return [
                'success' => false,
                'error' => "Courier service not found for {$courier->slug}",
            ];
        }

        if (!$courierService->supportsPaymentSync()) {
            return [
                'success' => false,
                'error' => "Payment sync is not implemented for {$courier->name}",
            ];
        }

        $result = $courierService->getCashoutStatement($dateFrom, $dateTo);

        if (!$result['success']) {
            Log::warning('Courier collection sync failed', [
                'courier_id' => $courier->id,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'error' => $result['error'] ?? 'Unknown error',
            ]);

            return $result;
        }

        $importResult = $this->reconciliationService->importFromCourier(
            $courier,
            $result['collections'] ?? [],
        );

        Log::info('Courier collection sync completed', [
            'courier_id' => $courier->id,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'api_total' => $result['total'] ?? 0,
            'imported' => $importResult['imported'] ?? 0,
            'reconciled' => $importResult['reconciled'] ?? 0,
            'skipped' => $importResult['skipped'] ?? 0,
        ]);

        return [
            'success' => true,
            'api_total' => $result['total'] ?? 0,
            'imported' => $importResult['imported'] ?? 0,
            'reconciled' => $importResult['reconciled'] ?? 0,
            'skipped' => $importResult['skipped'] ?? 0,
            'errors' => $importResult['errors'] ?? [],
            'batch_id' => $importResult['batch_id'] ?? null,
        ];
    }

    public function syncSingleOrder(Courier $courier, string $invoice): array
    {
        $courierService = $this->courierManager->get($courier->slug);

        if (!$courierService) {
            return [
                'success' => false,
                'error' => "Courier service not found for {$courier->slug}",
            ];
        }

        $result = $courierService->getCashoutDetailByInvoice($invoice);

        if (!$result['success']) {
            return $result;
        }

        $importResult = $this->reconciliationService->importFromCourier(
            $courier,
            [$result['detail']],
        );

        return [
            'success' => true,
            'imported' => $importResult['imported'] ?? 0,
            'reconciled' => $importResult['reconciled'] ?? 0,
        ];
    }
}
