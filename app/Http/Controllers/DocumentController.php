<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\Part;
use App\Models\PaymentReceipt;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SalesInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Picqer\Barcode\BarcodeGeneratorPNG;

class DocumentController extends Controller
{
    public function salesInvoicePdf(SalesInvoice $salesInvoice): Response
    {
        $salesInvoice->load(['items.part.brand', 'customer', 'branch']);

        $view = $salesInvoice->source === 'pos' ? pdf_view('pos-receipt') : pdf_view('sales-invoice');

        $pdf = Pdf::loadView($view, compact('salesInvoice'));

        $prefix = $salesInvoice->source === 'pos' ? 'pos-receipt' : 'sales-invoice';

        return $pdf->download($prefix.'-'.$salesInvoice->invoice_no.'.pdf');
    }

    public function purchaseInvoicePdf(PurchaseInvoice $purchaseInvoice): Response
    {
        $purchaseInvoice->load(['items.part.brand', 'vendor', 'branch', 'purchaseOrder']);

        $pdf = Pdf::loadView(pdf_view('purchase-invoice'), compact('purchaseInvoice'));

        return $pdf->download('purchase-invoice-'.$purchaseInvoice->invoice_no.'.pdf');
    }

    public function deliveryNotePdf(DeliveryNote $deliveryNote): Response
    {
        $deliveryNote->load(['items.part', 'customer', 'branch', 'salesInvoice']);

        $pdf = Pdf::loadView(pdf_view('delivery-note'), ['note' => $deliveryNote]);

        return $pdf->download('delivery-note-'.$deliveryNote->dn_no.'.pdf');
    }

    public function quotationPdf(Quotation $quotation): Response
    {
        $quotation->load(['items.part', 'customer', 'branch']);

        $pdf = Pdf::loadView(pdf_view('quotation'), compact('quotation'));

        return $pdf->download('quotation-'.$quotation->quotation_no.'.pdf');
    }

    public function purchaseOrderPdf(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load(['items.part', 'vendor', 'branch']);

        $pdf = Pdf::loadView(pdf_view('purchase-order'), ['order' => $purchaseOrder]);

        return $pdf->download('purchase-order-'.$purchaseOrder->po_no.'.pdf');
    }

    public function paymentReceiptPdf(PaymentReceipt $paymentReceipt): Response
    {
        $paymentReceipt->load(['customer', 'vendor', 'branch']);

        $pdf = Pdf::loadView(pdf_view('payment-receipt'), ['receipt' => $paymentReceipt]);

        return $pdf->download('receipt-'.$paymentReceipt->receipt_no.'.pdf');
    }

    public function payslipPdf(PayrollRun $payrollRun, PayrollItem $payrollItem): Response
    {
        abort_unless($payrollItem->payroll_run_id === $payrollRun->id, 404);

        $payrollItem->load('employee.department');
        $payrollRun->load('branch');

        $pdf = Pdf::loadView(pdf_view('payslip'), [
            'run' => $payrollRun,
            'item' => $payrollItem,
        ]);

        $name = $payrollItem->employee?->employee_no ?? $payrollItem->id;

        return $pdf->download('payslip-'.$payrollRun->payroll_no.'-'.$name.'.pdf');
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
