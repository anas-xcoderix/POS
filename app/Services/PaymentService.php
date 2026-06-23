<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\PaymentReceipt;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private AccountingService $accountingService,
        private FiscalPeriodService $fiscalPeriod,
    ) {}

    public function createReceipt(array $data): PaymentReceipt
    {
        return DB::transaction(function () use ($data) {
            $this->fiscalPeriod->assertOpen($data['receipt_date']);

            $receipt = PaymentReceipt::create($data);

            if ($receipt->sales_invoice_id) {
                $invoice = SalesInvoice::lockForUpdate()->find($receipt->sales_invoice_id);
                if ($invoice) {
                    $invoice->increment('paid_amount', (float) $receipt->amount);
                }
            }

            if ($receipt->purchase_invoice_id) {
                $invoice = PurchaseInvoice::lockForUpdate()->find($receipt->purchase_invoice_id);
                if ($invoice) {
                    $invoice->increment('paid_amount', (float) $receipt->amount);
                }
            }

            if ($receipt->customer_id) {
                Customer::where('id', $receipt->customer_id)->decrement('balance', (float) $receipt->amount);
            }

            if ($receipt->vendor_id) {
                Vendor::where('id', $receipt->vendor_id)->decrement('balance', (float) $receipt->amount);
            }

            $this->accountingService->postPaymentReceipt($receipt->fresh(['customer', 'vendor']), $data['created_by'] ?? null);

            return $receipt->fresh(['customer', 'vendor', 'branch']);
        });
    }

    public function customerStatement(int $customerId, ?string $from = null, ?string $to = null): Collection
    {
        $from = $from ?? now()->startOfYear()->toDateString();
        $to = $to ?? now()->toDateString();

        $invoices = SalesInvoice::where('customer_id', $customerId)
            ->where('status', 'posted')
            ->whereNull('voided_at')
            ->whereBetween('invoice_date', [$from, $to])
            ->get()
            ->map(fn ($inv) => [
                'date' => $inv->invoice_date->format('Y-m-d'),
                'type' => 'Invoice',
                'reference' => $inv->invoice_no,
                'debit' => (float) $inv->total_amount,
                'credit' => 0,
            ]);

        $receipts = PaymentReceipt::where('customer_id', $customerId)
            ->where('party_type', 'customer')
            ->whereBetween('receipt_date', [$from, $to])
            ->get()
            ->map(fn ($r) => [
                'date' => $r->receipt_date->format('Y-m-d'),
                'type' => 'Receipt',
                'reference' => $r->receipt_no,
                'debit' => 0,
                'credit' => (float) $r->amount,
            ]);

        return $invoices->concat($receipts)->sortBy('date')->values();
    }

    public function vendorStatement(int $vendorId, ?string $from = null, ?string $to = null): Collection
    {
        $from = $from ?? now()->startOfYear()->toDateString();
        $to = $to ?? now()->toDateString();

        $invoices = PurchaseInvoice::where('vendor_id', $vendorId)
            ->where('status', 'posted')
            ->whereNull('voided_at')
            ->whereBetween('invoice_date', [$from, $to])
            ->get()
            ->map(fn ($inv) => [
                'date' => $inv->invoice_date->format('Y-m-d'),
                'type' => 'Invoice',
                'reference' => $inv->invoice_no,
                'debit' => 0,
                'credit' => (float) $inv->total_amount,
            ]);

        $payments = PaymentReceipt::where('vendor_id', $vendorId)
            ->where('party_type', 'vendor')
            ->whereBetween('receipt_date', [$from, $to])
            ->get()
            ->map(fn ($r) => [
                'date' => $r->receipt_date->format('Y-m-d'),
                'type' => 'Payment',
                'reference' => $r->receipt_no,
                'debit' => (float) $r->amount,
                'credit' => 0,
            ]);

        return $invoices->concat($payments)->sortBy('date')->values();
    }
}
