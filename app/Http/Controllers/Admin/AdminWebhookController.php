<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\CourierWebhookLog;
use Illuminate\Http\Request;

class AdminWebhookController extends Controller
{
    public function index(Request $request)
    {
        $query = CourierWebhookLog::with(['courier:id,name,slug', 'shipment:id,shipment_number,consignment_id']);

        if ($status = $request->input('status')) {
            $query->where('is_processed', $status === 'processed');
        }

        if ($courierId = $request->input('courier_id')) {
            $query->where('courier_id', $courierId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('consignment_id', 'like', "%{$search}%")
                  ->orWhere('event_type', 'like', "%{$search}%")
                  ->orWhere('courier_slug', 'like', "%{$search}%")
                  ->orWhereHas('shipment', fn ($oq) => $oq->where('shipment_number', 'like', "%{$search}%"));
            });
        }

        $logs = $query->latest()->paginate(20)->withQueryString();
        $couriers = Courier::active()->orderBy('name')->get();

        $stats = [
            'total' => CourierWebhookLog::count(),
            'processed' => CourierWebhookLog::where('is_processed', true)->count(),
            'failed' => CourierWebhookLog::where('is_processed', false)->whereNotNull('error_message')->count(),
            'pending' => CourierWebhookLog::where('is_processed', false)->whereNull('error_message')->count(),
        ];

        return view('admin.webhooks.index', compact('logs', 'couriers', 'stats'));
    }

    public function show(CourierWebhookLog $log)
    {
        $log->load(['courier:id,name,slug', 'shipment:id,shipment_number,consignment_id,status']);

        return view('admin.webhooks.show', compact('log'));
    }

    public function retry(CourierWebhookLog $log)
    {
        if ($log->is_processed) {
            return back()->with('success', 'This webhook was already processed.');
        }

        $payload = $log->payload;
        $courierSlug = $log->courier_slug;

        $log->update(['processing_result' => 'Retrying...']);

        try {
            $response = \Illuminate\Support\Facades\Http::post(
                url("/api/webhooks/courier/{$courierSlug}"),
                $payload
            );

            if ($response->successful()) {
                $log->markProcessed('Retried successfully');
                return back()->with('success', 'Webhook retried successfully.');
            }

            $log->markFailed('Retry failed: HTTP ' . $response->status());
            return back()->withErrors(['error' => 'Retry failed with HTTP ' . $response->status()]);
        } catch (\Exception $e) {
            $log->markFailed('Retry exception: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Retry failed: ' . $e->getMessage()]);
        }
    }

    public function destroy(CourierWebhookLog $log)
    {
        $log->delete();
        return back()->with('success', 'Webhook log deleted.');
    }
}
