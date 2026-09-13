<?php

namespace App\Http\Controllers;

use App\Models\Courier;
use App\Models\CourierWebhookLog;
use App\Models\Shipment;
use App\Services\Courier\CourierManager;
use App\Services\ShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleSteadfast(Request $request): JsonResponse
    {
        return $this->processWebhook($request, 'steadfast');
    }

    public function handlePathao(Request $request): JsonResponse
    {
        return $this->processWebhook($request, 'pathao');
    }

    public function genericWebhook(Request $request, string $courierSlug): JsonResponse
    {
        return $this->processWebhook($request, $courierSlug);
    }

    private function processWebhook(Request $request, string $courierSlug): JsonResponse
    {
        $payload = $request->all();
        $headers = $request->headers->all();

        $courier = Courier::where('slug', $courierSlug)->where('is_active', true)->first();

        if (!$courier) {
            Log::warning("Webhook received for unknown/inactive courier: {$courierSlug}");
            return response()->json(['status' => 'error', 'message' => 'Courier not configured'], 404);
        }

        if (!$this->verifyWebhookSignature($request, $courier)) {
            Log::warning("Webhook signature verification failed for {$courierSlug}", [
                'ip' => $request->ip(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
        }

        $idempotencyKey = $this->generateIdempotencyKey($request, $courierSlug);

        if ($idempotencyKey && CourierWebhookLog::isDuplicate($idempotencyKey)) {
            Log::info("Duplicate webhook ignored for {$courierSlug}", [
                'idempotency_key' => $idempotencyKey,
            ]);
            return response()->json(['status' => 'success', 'message' => 'Already processed']);
        }

        $log = CourierWebhookLog::create([
            'courier_id' => $courier->id,
            'event_type' => $payload['event'] ?? $payload['type'] ?? $payload['status'] ?? 'unknown',
            'courier_slug' => $courierSlug,
            'consignment_id' => $this->extractConsignmentId($payload),
            'payload' => $payload,
            'headers' => array_filter($headers, fn ($k) => !in_array($k, ['authorization', 'cookie']), ARRAY_FILTER_USE_KEY),
            'ip_address' => $request->ip(),
            'idempotency_key' => $idempotencyKey,
        ]);

        try {
            $manager = app(CourierManager::class);
            $service = $manager->getService($courier);
            $parsed = $service->parseWebhookPayload($payload);

            if (empty($parsed['consignment_id'])) {
                $log->markFailed('No consignment ID in payload');
                return response()->json(['status' => 'error', 'message' => 'Invalid payload']);
            }

            $shipment = Shipment::where('consignment_id', $parsed['consignment_id'])->first();

            if (!$shipment) {
                $log->markFailed("No shipment found for consignment: {$parsed['consignment_id']}");
                return response()->json(['status' => 'error', 'message' => 'Shipment not found']);
            }

            $log->update(['shipment_id' => $shipment->id]);

            $newStatus = $parsed['status'];
            $oldStatus = $shipment->internal_status ?? $shipment->status;

            if ($newStatus !== $oldStatus) {
                $shipment->updateStatus($newStatus, $parsed['details'] ?? null, $parsed['data'] ?? null);

                $shipmentService = app(ShipmentService::class);

                match ($newStatus) {
                    'delivered' => $shipmentService->handleDelivery($shipment),
                    'returned' => $shipmentService->handleReturn($shipment),
                    'delivery_failed' => $shipmentService->handleFailedDelivery($shipment),
                    default => null,
                };

                Log::info("Webhook processed for {$courierSlug}", [
                    'shipment_id' => $shipment->id,
                    'consignment_id' => $parsed['consignment_id'],
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ]);
            }

            $log->markProcessed("Status updated from {$oldStatus} to {$newStatus}");

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error("Webhook processing failed for {$courierSlug}", [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            $log->markFailed($e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Processing failed'], 500);
        }
    }

    private function verifyWebhookSignature(Request $request, Courier $courier): bool
    {
        $signature = $request->header('X-Signature')
            ?? $request->header('X-Stepped-Signature')
            ?? $request->header('X-Pathao-Signature')
            ?? $request->header('X-Hub-Signature-256');

        if (!$signature) {
            return true;
        }

        $secret = $courier->webhook_secret_decrypted ?? $courier->api_secret_decrypted;

        if (!$secret) {
            Log::warning('Webhook signature verification skipped: no secret configured', [
                'courier_id' => $courier->id,
            ]);
            return true;
        }

        $expectedSignature = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expectedSignature, $signature)
            || hash_equals('sha256=' . $expectedSignature, $signature);
    }

    private function generateIdempotencyKey(Request $request, string $courierSlug): ?string
    {
        $payload = $request->all();

        $uniqueParts = [
            $courierSlug,
            $payload['invoice'] ?? $payload['consignment_id'] ?? $payload['order_id'] ?? '',
            $payload['status'] ?? $payload['event'] ?? $payload['type'] ?? '',
            $payload['timestamp'] ?? $payload['created_at'] ?? '',
        ];

        $key = implode(':', array_filter($uniqueParts));

        return $key ? hash('sha256', $key) : null;
    }

    private function extractConsignmentId(array $payload): ?string
    {
        return $payload['invoice']
            ?? $payload['consignment_id']
            ?? $payload['order_id']
            ?? $payload['data']['invoice']
            ?? $payload['data']['consignment_id']
            ?? $payload['data']['order_id']
            ?? null;
    }
}
