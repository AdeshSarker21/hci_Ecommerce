<?php

namespace App\Services\Courier;

use App\Models\Courier;
use App\Models\Shipment;

interface CourierInterface
{
    public function getName(): string;

    public function getSlug(): string;

    public function createShipment(Shipment $shipment): array;

    public function cancelShipment(Shipment $shipment): array;

    public function trackShipment(Shipment $shipment): array;

    public function trackByConsignmentId(string $consignmentId): array;

    public function validateAddress(array $address): array;

    public function testConnection(Courier $courier): array;

    public function parseWebhookPayload(array $payload): array;

    public function getCourierStatus(string $courierStatus): string;

    public function getCashoutStatement(string $dateFrom, string $dateTo, ?string $invoiceNo = null): array;

    public function getCashoutDetailByInvoice(string $invoice): array;

    public function supportsPaymentSync(): bool;
}
