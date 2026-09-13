<?php

namespace App\Jobs;

use App\Models\Shipment;
use App\Services\Courier\CourierManager;
use Illuminate\Support\Facades\Log;

class SyncShipmentStatusJob extends Job
{
    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public Shipment $shipment)
    {
    }

    public function handle(CourierManager $manager): void
    {
        if (!$this->shipment->consignment_id) {
            Log::info('SyncShipmentStatusJob: no consignment_id, skipping', [
                'shipment_id' => $this->shipment->id,
            ]);
            return;
        }

        $result = $manager->syncStatus($this->shipment);

        if ($result['success']) {
            Log::info('SyncShipmentStatusJob: status synced', [
                'shipment_id' => $this->shipment->id,
                'status' => $this->shipment->fresh()->status,
            ]);
        } else {
            Log::warning('SyncShipmentStatusJob: sync failed', [
                'shipment_id' => $this->shipment->id,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SyncShipmentStatusJob: permanently failed', [
            'shipment_id' => $this->shipment->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
