<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Location;
use App\Models\Part;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Picqer\Barcode\BarcodeGeneratorPNG;

class DocumentController extends Controller
{
    public function salesInvoicePdf(SalesInvoice $salesInvoice): Response
    {
        $salesInvoice->load(['items.part.brand', 'customer', 'branch']);

        $pdf = Pdf::loadView(pdf_view('sales-invoice'), compact('salesInvoice'));

        return $pdf->download('sales-invoice-'.$salesInvoice->invoice_no.'.pdf');
    }

    public function purchaseInvoicePdf(PurchaseInvoice $purchaseInvoice): Response
    {
        $purchaseInvoice->load(['items.part.brand', 'vendor', 'branch', 'purchaseOrder']);

        $pdf = Pdf::loadView(pdf_view('purchase-invoice'), compact('purchaseInvoice'));

        return $pdf->download('purchase-invoice-'.$purchaseInvoice->invoice_no.'.pdf');
    }

    public function partBarcode(Part $part): Response
    {
        $code = $part->barcode ?: $part->part_number;
        $generator = new BarcodeGeneratorPNG;
        $barcode = base64_encode($generator->getBarcode($code, $generator::TYPE_CODE_128, 2, 60));

        $pdf = Pdf::loadView(pdf_view('part-label'), compact('part', 'barcode', 'code'));

        return $pdf->download('label-'.$part->part_number.'.pdf');
    }

    public function partBarcodeImage(Part $part): Response
    {
        $code = $part->barcode ?: $part->part_number;
        $generator = new BarcodeGeneratorPNG;

        return response($generator->getBarcode($code, $generator::TYPE_CODE_128, 2, 80), 200, [
            'Content-Type' => 'image/png',
        ]);
    }
}
