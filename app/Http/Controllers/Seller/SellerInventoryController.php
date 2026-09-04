<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\InventoryService;
use App\Services\ProductIdentifierService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SellerInventoryController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService,
        private ProductIdentifierService $identifierService,
    ) {}

    public function index(Request $request)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $query = Product::where('seller_id', $seller->id)
            ->where('manage_stock', true)
            ->with(['category']);

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
            'total_tracked' => Product::where('seller_id', $seller->id)->where('manage_stock', true)->count(),
            'in_stock' => Product::where('seller_id', $seller->id)->where('manage_stock', true)->whereColumn('quantity', '>', 'reserved_quantity')->where('quantity', '>', 0)->count(),
            'low_stock' => Product::where('seller_id', $seller->id)->where('manage_stock', true)->whereColumn('quantity', '>', 'reserved_quantity')->whereColumn('quantity', '<=', 'low_stock_threshold')->where('quantity', '>', 0)->count(),
            'out_of_stock' => Product::where('seller_id', $seller->id)->where('manage_stock', true)->where(function ($q) {
                $q->whereColumn('quantity', '<=', 'reserved_quantity')->orWhere('quantity', '<=', 0);
            })->count(),
        ];

        return view('seller.inventory.index', compact('seller', 'products', 'stats'));
    }

    public function show(Product $product)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $product->seller_id !== $seller->id) {
            abort(403);
        }

        $transactions = $this->inventoryService->getHistory($product, 50);

        return view('seller.inventory.show', compact('seller', 'product', 'transactions'));
    }

    public function adjust(Request $request, Product $product)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $product->seller_id !== $seller->id) {
            abort(403);
        }

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
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $product->seller_id !== $seller->id) {
            abort(403);
        }

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
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $product->seller_id !== $seller->id) {
            abort(403);
        }

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
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $product->seller_id !== $seller->id) {
            abort(403);
        }

        $this->identifierService->generateBarcode($product);

        return back()->with('success', 'Barcode generated successfully.');
    }

    public function generateQrCode(Product $product)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $product->seller_id !== $seller->id) {
            abort(403);
        }

        $this->identifierService->generateQrCode($product);

        return back()->with('success', 'QR Code generated successfully.');
    }

    public function printIdentifiers(Product $product)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $product->seller_id !== $seller->id) {
            abort(403);
        }

        $this->identifierService->generateBarcode($product);
        $this->identifierService->generateQrCode($product);

        return view('seller.inventory.print', compact('seller', 'product'));
    }
}
