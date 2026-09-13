<?php

namespace App\Jobs;

use App\Models\Shipment;
use App\Services\Courier\CourierManager;
use Illuminate\Support\Facades\Log;

class CreateShipmentJob extends Job
{
    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(public Shipment $shipment)
    {
    }

    public function handle(CourierManager $manager): void
    {
        if ($this->shipment->consignment_id) {
            Log::info('CreateShipmentJob: shipment already has consignment_id, skipping', [
                'shipment_id' => $this->shipment->id,
            ]);
            return;
        }

        $result = $manager->createShipment($this->shipment, $this->shipment->courier);

        if ($result['success']) {
            $this->shipment->update([
                'consignment_id' => $result['consignment_id'] ?? null,
                'tracking_code' => $result['tracking_code'] ?? null,
                'invoice_number' => $result['invoice_number'] ?? null,
                'courier_response' => $result['courier_response'] ?? null,
                'courier_status' => $result['status'] ?? 'pending',
                'status' => $result['status'] ?? 'pending',
            ]);

            Log::info('CreateShipmentJob: shipment created successfully', [
                'shipment_id' => $this->shipment->id,
                'consignment_id' => $this->shipment->consignment_id,
            ]);
        } else {
            $this->shipment->update([
                'error_message' => $result['error'] ?? 'Unknown error',
            ]);

            Log::error('CreateShipmentJob: failed to create shipment', [
                'shipment_id' => $this->shipment->id,
                'error' => $result['error'] ?? 'Unknown error',
            ]);

            throw new \RuntimeException('Failed to create shipment: ' . ($result['error'] ?? 'Unknown error'));
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('CreateShipmentJob: permanently failed', [
            'shipment_id' => $this->shipment->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
