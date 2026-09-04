<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Services\InventoryService;
use App\Services\ProductIdentifierService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InventoryController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService,
        private ProductIdentifierService $identifierService,
    ) {}

    public function index(Request $request)
    {
        $query = Product::with(['seller', 'category'])
            ->where('manage_stock', true);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('stock_status')) {
            switch ($status) {
                case 'in_stock':
                    $query->whereColumn('quantity', '>', 'reserved_quantity')
                          ->where('quantity', '>', 0);
                    break;
                case 'low_stock':
                    $query->whereColumn('quantity', '>', 'reserved_quantity')
                          ->whereColumn('quantity', '<=', 'low_stock_threshold')
                          ->where('quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where(function ($q) {
                        $q->whereColumn('quantity', '<=', 'reserved_quantity')
                          ->orWhere('quantity', '<=', 0);
                    });
                    break;
            }
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total_tracked' => Product::where('manage_stock', true)->count(),
            'in_stock' => Product::where('manage_stock', true)->whereColumn('quantity', '>', 'reserved_quantity')->where('quantity', '>', 0)->count(),
            'low_stock' => Product::where('manage_stock', true)->whereColumn('quantity', '>', 'reserved_quantity')->whereColumn('quantity', '<=', 'low_stock_threshold')->where('quantity', '>', 0)->count(),
            'out_of_stock' => Product::where('manage_stock', true)->where(function ($q) {
                $q->whereColumn('quantity', '<=', 'reserved_quantity')->orWhere('quantity', '<=', 0);
            })->count(),
        ];

        return view('admin.inventory.index', compact('products', 'stats'));
    }

    public function show(Product $product)
    {
        $transactions = $this->inventoryService->getHistory($product, 50);

        return view('admin.inventory.show', compact('product', 'transactions'));
    }

    public function adjust(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->inventoryService->adjustStock(
            $product,
            $validated['quantity'],
            $validated['notes'] ?? null,
            auth()->user()
        );

        return back()->with('success', 'Stock adjusted successfully.');
    }

    public function addStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->inventoryService->addStock(
            $product,
            $validated['quantity'],
            $validated['notes'] ?? null,
            $validated['unit_cost'] ?? null,
            null,
            null,
            auth()->user()
        );

        return back()->with('success', 'Stock added successfully.');
    }

    public function removeStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'string', 'in:sale,return,cancellation,damaged,lost'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->inventoryService->removeStock(
            $product,
            $validated['quantity'],
            $validated['type'],
            $validated['notes'] ?? null,
            null,
            null,
            auth()->user()
        );

        return back()->with('success', 'Stock removed successfully.');
    }

    public function generateBarcode(Product $product)
    {
        $url = $this->identifierService->generateBarcode($product);

        return back()->with('success', 'Barcode generated successfully.');
    }

    public function generateQrCode(Product $product)
    {
        $url = $this->identifierService->generateQrCode($product);

        return back()->with('success', 'QR Code generated successfully.');
    }

    public function printIdentifiers(Product $product)
    {
        $this->identifierService->generateBarcode($product);
        $this->identifierService->generateQrCode($product);

        return view('admin.inventory.print', compact('product'));
    }

    public function barcodeSvg(Product $product): Response
    {
        $svg = $this->identifierService->getBarcodeSvg($product);

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }

    public function qrCodeSvg(Product $product): Response
    {
        $svg = $this->identifierService->getQrCodeSvg($product);

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }
}
