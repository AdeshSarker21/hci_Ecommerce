<?php

namespace App\Services\Courier;

use App\Models\Courier;
use App\Models\Shipment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PathaoCourierService implements CourierInterface
{
    private Courier $courier;
    private string $baseUrl;
    private ?string $accessToken;
    private ?string $tokenExpiresAt;

    private const STATUS_MAP = [
        'pending' => 'pending',
        'assigned' => 'pending',
        'picked' => 'picked_up',
        'picked_up' => 'picked_up',
        'in_transit' => 'in_transit',
        'transit' => 'in_transit',
        'reached_at_hub' => 'in_transit',
        'delivered' => 'delivered',
        'partial_delivered' => 'delivered',
        'returned' => 'returned',
        'return_requested' => 'returned',
        'cancelled' => 'cancelled',
        'canceled' => 'cancelled',
        'on_hold' => 'in_transit',
        'failed' => 'failed',
        'not_delivered' => 'failed',
    ];

    public function __construct(Courier $courier)
    {
        $this->courier = $courier;
        $this->baseUrl = rtrim($courier->api_base_url ?? 'https://merchant-api-np.pathao.com', '/');
        $this->accessToken = $courier->access_token;
        $this->tokenExpiresAt = $courier->token_expires_at;
    }

    public function getName(): string
    {
        return 'Pathao Courier';
    }

    public function getSlug(): string
    {
        return 'pathao';
    }

    public function createShipment(Shipment $shipment): array
    {
        try {
            if (!$this->ensureAuthenticated()) {
                return ['success' => false, 'error' => 'Failed to authenticate with Pathao API'];
            }

            $payload = [
                'merchant_order_id' => $shipment->shipment_number,
                'recipient_name' => $shipment->recipient_name,
                'recipient_phone' => $shipment->recipient_phone,
                'recipient_address' => $shipment->recipient_address,
                'cod_amount' => (float) $shipment->cod_amount,
                'note' => $shipment->note ?? '',
            ];

            if ($shipment->recipient_city) {
                $payload['city'] = $shipment->recipient_city;
            }
            if ($shipment->recipient_area) {
                $payload['area'] = $shipment->recipient_area;
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json',
            ])->timeout(30)->post("{$this->baseUrl}/api/v1/create-order", $payload);

            $data = $response->json();

            if ($response->successful() && isset($data['data']['consignment_id'])) {
                return [
                    'success' => true,
                    'consignment_id' => (string) $data['data']['consignment_id'],
                    'tracking_code' => $data['data']['tracking_code'] ?? null,
                    'invoice_number' => $shipment->shipment_number,
                    'courier_response' => $data,
                    'status' => 'pending',
                ];
            }

            return [
                'success' => false,
                'error' => $data['message'] ?? $data['error'] ?? 'Failed to create Pathao shipment',
                'courier_response' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Pathao create shipment failed', [
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
            if (!$this->ensureAuthenticated()) {
                return ['success' => false, 'error' => 'Failed to authenticate'];
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json',
            ])->timeout(30)->post("{$this->baseUrl}/api/v1/orders/{$shipment->consignment_id}/cancel");

            $data = $response->json();

            return [
                'success' => $response->successful(),
                'message' => $data['message'] ?? null,
                'courier_response' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Pathao cancel shipment failed', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
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
            if (!$this->ensureAuthenticated()) {
                return ['success' => false, 'error' => 'Failed to authenticate'];
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
            ])->timeout(30)->get("{$this->baseUrl}/api/v1/orders/{$consignmentId}/tracking");

            $data = $response->json();

            if ($response->successful() && isset($data['data'])) {
                $trackingData = $data['data'];
                $courierStatus = $trackingData['status'] ?? $trackingData['current_status'] ?? '';

                return [
                    'success' => true,
                    'status' => $this->getCourierStatus($courierStatus),
                    'courier_status' => $courierStatus,
                    'details' => $trackingData['status_update'] ?? $trackingData['tracking'] ?? null,
                    'data' => $trackingData,
                ];
            }

            return [
                'success' => false,
                'error' => $data['message'] ?? 'Tracking failed',
                'courier_response' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Pathao tracking failed', [
                'consignment_id' => $consignmentId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function validateAddress(array $address): array
    {
        return ['success' => true, 'validated' => true];
    }

    public function testConnection(Courier $courier): array
    {
        try {
            $config = $courier->api_config ?? [];
            $clientId = $config['client_id'] ?? $courier->api_key_decrypted;
            $clientSecret = $config['client_secret'] ?? $courier->api_secret_decrypted;

            if (!$clientId || !$clientSecret) {
                return [
                    'success' => false,
                    'error' => 'Client ID and Client Secret are required',
                ];
            }

            $response = Http::asForm()->timeout(15)->post("{$this->baseUrl}/auth/realms/pathao/protocol/openid-connect/token", [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['access_token'])) {
                $courier->update([
                    'access_token' => $data['access_token'],
                    'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
                    'last_synced_at' => now(),
                ]);

                return [
                    'success' => true,
                    'status_code' => $response->status(),
                    'message' => 'Connection successful. Token obtained.',
                ];
            }

            return [
                'success' => false,
                'error' => $data['error_description'] ?? $data['message'] ?? 'Authentication failed',
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
        $consignmentId = $payload['consignment_id']
            ?? $payload['data']['consignment_id']
            ?? $payload['order_id']
            ?? $payload['data']['order_id']
            ?? null;

        $status = $payload['status']
            ?? $payload['data']['status']
            ?? $payload['current_status']
            ?? $payload['data']['current_status']
            ?? null;

        return [
            'consignment_id' => $consignmentId,
            'courier_status' => $status,
            'status' => $this->getCourierStatus($status ?? ''),
            'details' => $payload['status_update'] ?? $payload['data']['status_update'] ?? null,
            'data' => $payload,
        ];
    }

    public function getCourierStatus(string $courierStatus): string
    {
        return self::STATUS_MAP[strtolower($courierStatus)] ?? 'pending';
    }

    public function supportsPaymentSync(): bool
    {
        return false;
    }

    public function getCashoutStatement(string $dateFrom, string $dateTo, ?string $invoiceNo = null): array
    {
        return [
            'success' => false,
            'error' => 'Cashout statement sync is not supported by Pathao Courier API',
        ];
    }

    public function getCashoutDetailByInvoice(string $invoice): array
    {
        return [
            'success' => false,
            'error' => 'Cashout detail sync is not supported by Pathao Courier API',
        ];
    }

    private function ensureAuthenticated(): bool
    {
        if ($this->accessToken && $this->tokenExpiresAt && now()->lessThan($this->tokenExpiresAt)) {
            return true;
        }

        return $this->authenticate();
    }

    private function authenticate(): bool
    {
        try {
            $config = $this->courier->api_config ?? [];
            $clientId = $config['client_id'] ?? $this->courier->api_key_decrypted;
            $clientSecret = $config['client_secret'] ?? $this->courier->api_secret_decrypted;
            $username = $config['username'] ?? null;
            $password = $config['password'] ?? null;

            if (!$clientId || !$clientSecret) {
                Log::warning('Pathao authentication failed: missing credentials', [
                    'courier_id' => $this->courier->id,
                ]);
                return false;
            }

            $params = [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ];

            if ($username && $password) {
                $params['grant_type'] = 'password';
                $params['username'] = $username;
                $params['password'] = $password;
            }

            $response = Http::asForm()->timeout(15)->post(
                "{$this->baseUrl}/auth/realms/pathao/protocol/openid-connect/token",
                $params
            );

            $data = $response->json();

            if ($response->successful() && isset($data['access_token'])) {
                $this->accessToken = $data['access_token'];
                $this->tokenExpiresAt = now()->addSeconds($data['expires_in'] ?? 3600);

                $this->courier->update([
                    'access_token' => $this->accessToken,
                    'token_expires_at' => $this->tokenExpiresAt,
                ]);

                return true;
            }

            Log::error('Pathao authentication failed', [
                'courier_id' => $this->courier->id,
                'response' => $data,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Pathao authentication exception', [
                'courier_id' => $this->courier->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
