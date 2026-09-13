<?php

namespace App\Services\Courier;

use App\Models\Courier;
use App\Models\Shipment;
use Illuminate\Support\Facades\Log;

class CourierManager
{
    private array $services = [];

    public function __construct()
    {
        $this->registerDefaults();
    }

    private function registerDefaults(): void
    {
        $this->services['steadfast'] = SteadfastCourierService::class;
        $this->services['pathao'] = PathaoCourierService::class;
    }

    public function register(string $slug, string $serviceClass): void
    {
        $this->services[$slug] = $serviceClass;
    }

    public function getService(?Courier $courier = null): CourierInterface
    {
        $courier = $courier ?? Courier::getDefault();

        if (!$courier) {
            throw new \RuntimeException('No active courier configured');
        }

        $serviceClass = $this->services[$courier->slug] ?? null;

        if (!$serviceClass) {
            throw new \RuntimeException("No service class registered for courier: {$courier->slug}");
        }

        return new $serviceClass($courier);
    }

    public function getServiceBySlug(string $slug): CourierInterface
    {
        $courier = Courier::getBySlug($slug);

        if (!$courier) {
            throw new \RuntimeException("Courier not found: {$slug}");
        }

        return $this->getService($courier);
    }

    public function createShipment(Shipment $shipment, ?Courier $courier = null): array
    {
        $service = $this->getService($courier);
        $result = $service->createShipment($shipment);

        Log::info("Courier shipment created via {$service->getSlug()}", [
            'shipment_id' => $shipment->id,
            'success' => $result['success'] ?? false,
        ]);

        return $result;
    }

    public function cancelShipment(Shipment $shipment): array
    {
        $service = $this->getService($shipment->courier);
        return $service->cancelShipment($shipment);
    }

    public function trackShipment(Shipment $shipment): array
    {
        $service = $this->getService($shipment->courier);
        return $service->trackShipment($shipment);
    }

    public function syncStatus(Shipment $shipment): array
    {
        $result = $this->trackShipment($shipment);

        if ($result['success']) {
            $newStatus = $result['status'];
            if ($newStatus !== $shipment->status) {
                $shipment->updateStatus(
                    $newStatus,
                    $result['details'] ?? null,
                    $result['data'] ?? null
                );
            }
        }

        return $result;
    }

    public function getCourierStatusMapping(string $slug, string $courierStatus): string
    {
        $service = $this->getServiceBySlug($slug);
        return $service->getCourierStatus($courierStatus);
    }
}
