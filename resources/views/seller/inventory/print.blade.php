<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Labels - {{ $product->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; padding: 20px; }
        .label { border: 2px dashed #d1d5db; border-radius: 8px; padding: 16px; margin-bottom: 16px; display: inline-block; text-align: center; page-break-inside: avoid; }
        .label h3 { font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #111827; }
        .label p { font-size: 12px; color: #6b7280; margin-bottom: 4px; }
        .label .sku { font-family: monospace; font-size: 14px; font-weight: 700; color: #1f2937; }
        .barcode, .qr { margin: 8px 0; }
        .barcode img, .qr img { max-width: 200px; height: auto; }
        .qr img { max-width: 120px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            .label { border: 1px solid #000; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #059669; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">Print Labels</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #6b7280; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; margin-left: 8px;">Close</button>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 16px;">
        <div class="label">
            <h3>{{ $product->name }}</h3>
            <p class="sku">{{ $product->sku }}</p>
            @if($product->barcode)
                <p>Barcode: {{ $product->barcode }}</p>
            @endif
            <div class="barcode">
                <img src="{{ route('seller.inventory.show', $product) }}" alt="Barcode" onerror="this.style.display='none'">
            </div>
            @if($product->price)
                <p style="font-weight: 700; font-size: 16px; color: #059669;">{{ $product->formatted_price }}</p>
            @endif
        </div>

        <div class="label">
            <h3>{{ $product->name }}</h3>
            <p class="sku">{{ $product->sku }}</p>
            <div class="qr">
                <p>QR Code will be available after generation</p>
            </div>
            <p>Scan for product details</p>
        </div>
    </div>
</body>
</html>
