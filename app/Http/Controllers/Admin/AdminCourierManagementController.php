<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Services\Courier\CourierManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminCourierManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Courier::withCount('shipments');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        $couriers = $query->orderBy('sort_order')->orderBy('name')->paginate(15)->withQueryString();

        $stats = [
            'total' => Courier::count(),
            'active' => Courier::where('is_active', true)->count(),
            'inactive' => Courier::where('is_active', false)->count(),
            'default' => Courier::where('is_default', true)->value('name') ?? 'None',
        ];

        return view('admin.couriers.index', compact('couriers', 'stats'));
    }

    public function create()
    {
        return view('admin.couriers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:couriers,slug',
            'code' => 'required|string|max:50|unique:couriers,code',
            'description' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|string|max:500',
            'api_key' => 'nullable|string|max:500',
            'api_secret' => 'nullable|string|max:500',
            'api_base_url' => 'nullable|url|max:255',
            'webhook_secret' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'cod_support' => 'boolean',
            'tracking_support' => 'boolean',
            'shipment_creation_support' => 'boolean',
            'return_support' => 'boolean',
            'cod_fee' => 'nullable|numeric|min:0',
            'cod_percentage' => 'nullable|numeric|min:0|max:100',
            'weight_limit_kg' => 'nullable|numeric|min:0',
            'max_value' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'config' => 'nullable|array',
        ]);

        $validated['slug'] = strtolower($validated['slug']);
        $validated['code'] = strtoupper($validated['code']);

        if (!empty($validated['is_default']) && $validated['is_default']) {
            Courier::where('is_default', true)->update(['is_default' => false]);
        }

        $validated['supported_services'] = array_filter([
            'cod' => $validated['cod_support'] ?? true,
            'tracking' => $validated['tracking_support'] ?? true,
            'shipment_creation' => $validated['shipment_creation_support'] ?? true,
            'return' => $validated['return_support'] ?? true,
        ]);

        $courier = Courier::create($validated);

        Log::info("Courier created: {$courier->name}", ['courier_id' => $courier->id]);

        return redirect()->route('admin.couriers.show', $courier)
            ->with('success', "Courier \"{$courier->name}\" created successfully.");
    }

    public function show(Courier $courier)
    {
        $courier->loadCount('shipments');

        $recentShipments = $courier->shipments()
            ->with('order:id,order_number')
            ->latest()
            ->limit(10)
            ->get();

        $recentWebhooks = $courier->webhookLogs()
            ->latest()
            ->limit(10)
            ->get();

        $stats = [
            'total_shipments' => $courier->shipments()->count(),
            'active_shipments' => $courier->shipments()->active()->count(),
            'delivered' => $courier->shipments()->where('internal_status', 'delivered')->count(),
            'returned' => $courier->shipments()->where('internal_status', 'returned')->count(),
            'total_webhooks' => $courier->webhookLogs()->count(),
            'failed_webhooks' => $courier->webhookLogs()->where('is_processed', false)->whereNotNull('error_message')->count(),
        ];

        return view('admin.couriers.show', compact('courier', 'recentShipments', 'recentWebhooks', 'stats'));
    }

    public function edit(Courier $courier)
    {
        return view('admin.couriers.edit', compact('courier'));
    }

    public function update(Request $request, Courier $courier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|string|max:500',
            'api_key' => 'nullable|string|max:500',
            'api_secret' => 'nullable|string|max:500',
            'api_base_url' => 'nullable|url|max:255',
            'webhook_secret' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'cod_support' => 'boolean',
            'tracking_support' => 'boolean',
            'shipment_creation_support' => 'boolean',
            'return_support' => 'boolean',
            'cod_fee' => 'nullable|numeric|min:0',
            'cod_percentage' => 'nullable|numeric|min:0|max:100',
            'weight_limit_kg' => 'nullable|numeric|min:0',
            'max_value' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'config' => 'nullable|array',
        ]);

        if (!empty($validated['is_default']) && $validated['is_default']) {
            Courier::where('is_default', true)->where('id', '!=', $courier->id)->update(['is_default' => false]);
        }

        $validated['supported_services'] = array_filter([
            'cod' => $validated['cod_support'] ?? $courier->cod_support,
            'tracking' => $validated['tracking_support'] ?? $courier->tracking_support,
            'shipment_creation' => $validated['shipment_creation_support'] ?? $courier->shipment_creation_support,
            'return' => $validated['return_support'] ?? $courier->return_support,
        ]);

        $courier->update($validated);

        Log::info("Courier updated: {$courier->name}", ['courier_id' => $courier->id]);

        return redirect()->route('admin.couriers.show', $courier)
            ->with('success', "Courier \"{$courier->name}\" updated successfully.");
    }

    public function destroy(Courier $courier)
    {
        if ($courier->shipments()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete a courier with existing shipments.']);
        }

        $name = $courier->name;
        $courier->delete();

        Log::info("Courier deleted: {$name}");

        return redirect()->route('admin.couriers.index')
            ->with('success', "Courier \"{$name}\" deleted.");
    }

    public function toggleStatus(Courier $courier)
    {
        $courier->update(['is_active' => !$courier->is_active]);

        $status = $courier->is_active ? 'activated' : 'deactivated';
        Log::info("Courier {$status}: {$courier->name}");

        return back()->with('success', "Courier \"{$courier->name}\" {$status}.");
    }

    public function setDefault(Courier $courier)
    {
        Courier::where('is_default', true)->update(['is_default' => false]);
        $courier->update(['is_default' => true]);

        return back()->with('success', "Courier \"{$courier->name}\" set as default.");
    }

    public function testConnection(Courier $courier)
    {
        try {
            $manager = app(CourierManager::class);
            $service = $manager->getService($courier);
            $result = $service->testConnection($courier);

            if ($result['success']) {
                $courier->update(['last_synced_at' => now()]);
                return back()->with('success', "API connection to \"{$courier->name}\" successful. " . ($result['message'] ?? ''));
            }

            return back()->withErrors(['error' => "Connection failed: " . ($result['error'] ?? 'Unknown error')]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => "Connection test failed: {$e->getMessage()}"]);
        }
    }

    public function apiSettings()
    {
        $couriers = Courier::orderBy('name')->get();
        return view('admin.couriers.api-settings', compact('couriers'));
    }

    public function updateApiSettings(Request $request, Courier $courier)
    {
        $validated = $request->validate([
            'api_key' => 'nullable|string|max:500',
            'api_secret' => 'nullable|string|max:500',
            'api_base_url' => 'nullable|url|max:255',
            'webhook_secret' => 'nullable|string|max:500',
            'config' => 'nullable|array',
            'config.client_id' => 'nullable|string|max:500',
            'config.client_secret' => 'nullable|string|max:500',
            'config.username' => 'nullable|string|max:255',
            'config.password' => 'nullable|string|max:255',
        ]);

        $updateData = [];
        if ($request->has('api_key')) {
            $updateData['api_key'] = $validated['api_key'] ?? null;
        }
        if ($request->has('api_secret')) {
            $updateData['api_secret'] = $validated['api_secret'] ?? null;
        }
        if (isset($validated['api_base_url'])) {
            $updateData['api_base_url'] = $validated['api_base_url'];
        }
        if ($request->has('webhook_secret')) {
            $updateData['webhook_secret'] = $validated['webhook_secret'] ?? null;
        }
        if (isset($validated['config'])) {
            $updateData['config'] = array_merge(
                $courier->config ?? [],
                $validated['config']
            );
        }

        $courier->update($updateData);

        return back()->with('success', "API settings for \"{$courier->name}\" updated.");
    }
}
