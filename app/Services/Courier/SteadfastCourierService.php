<?php

namespace App\Services\Courier;

use App\Models\Courier;
use App\Models\Shipment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SteadfastCourierService implements CourierInterface
{
    private Courier $courier;
    private string $apiKey;
    private string $secret;
    private string $baseUrl;

    public function __construct(Courier $courier)
    {
        $this->courier = $courier;
        $this->apiKey = $courier->api_key_decrypted;
        $this->secret = $courier->api_secret_decrypted;
        $this->baseUrl = rtrim($courier->api_base_url ?? 'https://api.steadfast.com.bd', '/');
    }

    public function getName(): string
    {
        return 'Steadfast Courier';
    }

    public function getSlug(): string
    {
        return 'steadfast';
    }

    public function createShipment(Shipment $shipment): array
    {
        try {
            $payload = [
                'invoice' => $shipment->shipment_number,
                'recipient_name' => $shipment->recipient_name,
                'recipient_phone' => $shipment->recipient_phone,
                'recipient_address' => $shipment->recipient_address,
                'cod_amount' => (float) $shipment->cod_amount,
                'note' => $shipment->note ?? '',
            ];

            if ($shipment->recipient_area) {
                $payload['recipient_area'] = $shipment->recipient_area;
            }
            if ($shipment->recipient_city) {
                $payload['recipient_city'] = $shipment->recipient_city;
            }
            if ($shipment->recipient_zone) {
                $payload['recipient_zone'] = $shipment->recipient_zone;
            }
            if ($shipment->pickup_address) {
                $payload['pickup_address'] = $shipment->pickup_address;
            }
            if ($shipment->pickup_area) {
                $payload['pickup_area'] = $shipment->pickup_area;
            }
            if ($shipment->weight_kg) {
                $payload['quantity'] = $shipment->quantity ?? 1;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Api-Key' => $this->apiKey,
                'Secret-Key' => $this->secret,
            ])->timeout(30)->post("{$this->baseUrl}/create_order", $payload);

            $data = $response->json();

            if ($response->successful() && isset($data['data'])) {
                return [
                    'success' => true,
                    'consignment_id' => $data['data']['consignment_id'] ?? null,
                    'tracking_code' => $data['data']['tracking_code'] ?? null,
                    'invoice_number' => $data['data']['invoice'] ?? $shipment->shipment_number,
                    'courier_response' => $data,
                    'status' => 'pending',
                ];
            }

            return [
                'success' => false,
                'error' => $data['message'] ?? 'Failed to create shipment',
                'courier_response' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Steadfast create shipment failed', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function cancelShipment(Shipment $shipment): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Api-Key' => $this->apiKey,
                'Secret-Key' => $this->secret,
            ])->timeout(30)->post("{$this->baseUrl}/cancel_order", [
                'consignment_id' => $shipment->consignment_id,
            ]);

            $data = $response->json();

            return [
                'success' => $response->successful(),
                'message' => $data['message'] ?? null,
                'courier_response' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Steadfast cancel shipment failed', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function trackShipment(Shipment $shipment): array
    {
        if (!$shipment->consignment_id) {
            return ['success' => false, 'error' => 'No consignment ID'];
        }

        return $this->trackByConsignmentId($shipment->consignment_id);
    }

    public function trackByConsignmentId(string $consignmentId): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Api-Key' => $this->apiKey,
                'Secret-Key' => $this->secret,
            ])->timeout(30)->get("{$this->baseUrl}/status_by_invoice/{$consignmentId}");

            $data = $response->json();

            if ($response->successful() && isset($data['data'])) {
                $trackingData = $data['data'];
                return [
                    'success' => true,
                    'status' => $this->getCourierStatus($trackingData['status'] ?? ''),
                    'courier_status' => $trackingData['status'] ?? null,
                    'details' => $trackingData['status_update'] ?? null,
                    'data' => $trackingData,
                ];
            }

            return [
                'success' => false,
                'error' => $data['message'] ?? 'Tracking failed',
                'courier_response' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Steadfast tracking failed', [
                'consignment_id' => $consignmentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function validateAddress(array $address): array
    {
        return ['success' => true, 'validated' => true];
    }

    public function testConnection(Courier $courier): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Api-Key' => $courier->api_key_decrypted,
                'Secret-Key' => $courier->api_secret_decrypted,
            ])->timeout(15)->get("{$this->baseUrl}/cities");

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'message' => $response->successful() ? 'Connection successful' : 'Connection failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function parseWebhookPayload(array $payload): array
    {
        $invoice = $payload['invoice'] ?? $payload['data']['invoice'] ?? null;
        $status = $payload['status'] ?? $payload['data']['status'] ?? null;

        return [
            'consignment_id' => $invoice,
            'courier_status' => $status,
            'status' => $this->getCourierStatus($status ?? ''),
            'details' => $payload['status_update'] ?? $payload['data']['status_update'] ?? null,
            'data' => $payload,
        ];
    }

    public function getCourierStatus(string $courierStatus): string
    {
        return match (strtolower($courierStatus)) {
            'pending' => 'pending',
            'picked' => 'picked_up',
            'picked_up' => 'picked_up',
            'in_transit' => 'in_transit',
            'transit' => 'in_transit',
            'delivered' => 'delivered',
            'delivery_complete' => 'delivered',
            'partial_delivered' => 'delivered',
            'returned' => 'returned',
            'return' => 'returned',
            'hold' => 'in_transit',
            'cancelled' => 'cancelled',
            'not_delivered' => 'failed',
            default => 'pending',
        };
    }

    public function supportsPaymentSync(): bool
    {
        return true;
    }

    public function getCashoutStatement(string $dateFrom, string $dateTo, ?string $invoiceNo = null): array
    {
        try {
            $params = [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ];

            if ($invoiceNo) {
                $params['invoice'] = $invoiceNo;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Api-Key' => $this->apiKey,
                'Secret-Key' => $this->secret,
            ])->timeout(30)->get("{$this->baseUrl}/get_cashout_statement", $params);

            $data = $response->json();

            if ($response->successful() && isset($data['data'])) {
                $collections = [];
                foreach ($data['data'] as $item) {
                    $collections[] = $this->normalizeCashoutItem($item);
                }

                return [
                    'success' => true,
                    'collections' => $collections,
                    'total' => count($collections),
                    'raw' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => $data['message'] ?? 'Failed to fetch cashout statement',
                'raw' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Steadfast cashout statement fetch failed', [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getCashoutDetailByInvoice(string $invoice): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Api-Key' => $this->apiKey,
                'Secret-Key' => $this->secret,
            ])->timeout(30)->get("{$this->baseUrl}/get_cashout_detail", [
                'invoice' => $invoice,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['data'])) {
                return [
                    'success' => true,
                    'detail' => $this->normalizeCashoutItem($data['data']),
                    'raw' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => $data['message'] ?? 'Failed to fetch cashout detail',
                'raw' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Steadfast cashout detail fetch failed', [
                'invoice' => $invoice,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function normalizeCashoutItem(array $item): array
    {
        return [
            'invoice' => $item['invoice'] ?? null,
            'consignment_id' => $item['consignment_id'] ?? null,
            'cod_amount' => (float) ($item['cod_amount'] ?? $item['total_collected'] ?? 0),
            'courier_charge' => (float) ($item['courier_charge'] ?? $item['delivery_charge'] ?? $item['service_charge'] ?? 0),
            'net_collected' => (float) ($item['net_collected'] ?? $item['cashout_amount'] ?? $item['payable_amount'] ?? 0),
            'status' => $item['status'] ?? $item['cashout_status'] ?? null,
            'payment_date' => $item['payment_date'] ?? $item['cashout_date'] ?? null,
            'payment_reference' => $item['payment_reference'] ?? $item['cashout_reference'] ?? null,
            'recipient_name' => $item['recipient_name'] ?? null,
            'recipient_phone' => $item['recipient_phone'] ?? null,
            'order_date' => $item['order_date'] ?? $item['created_at'] ?? null,
            'raw' => $item,
        ];
    }
}
