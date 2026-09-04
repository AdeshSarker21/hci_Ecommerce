<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorSVG;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class ProductIdentifierService
{
    public function generateBarcode(Product $product): string
    {
        $generator = new BarcodeGeneratorSVG();
        $barcode = $generator->getBarcode($product->sku, BarcodeGeneratorSVG::TYPE_CODE_128);

        $path = 'barcodes/' . $product->sku . '.svg';
        Storage::disk('public')->put($path, $barcode);

        return Storage::disk('public')->url($path);
    }

    public function generateQrCode(Product $product): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        $url = route('product.show', $product->slug);
        $qrCode = $writer->writeString($url);

        $path = 'qr-codes/' . $product->sku . '.svg';
        Storage::disk('public')->put($path, $qrCode);

        return Storage::disk('public')->url($path);
    }

    public function getBarcodeSvg(Product $product): string
    {
        $generator = new BarcodeGeneratorSVG();
        return $generator->getBarcode($product->sku, BarcodeGeneratorSVG::TYPE_CODE_128);
    }

    public function getQrCodeSvg(Product $product): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        $url = route('product.show', $product->slug);
        return $writer->writeString($url);
    }

    public function regenerateBarcode(Product $product): string
    {
        $path = 'barcodes/' . $product->sku . '.svg';
        Storage::disk('public')->delete($path);

        return $this->generateBarcode($product);
    }

    public function regenerateQrCode(Product $product): string
    {
        $path = 'qr-codes/' . $product->sku . '.svg';
        Storage::disk('public')->delete($path);

        return $this->generateQrCode($product);
    }
}
