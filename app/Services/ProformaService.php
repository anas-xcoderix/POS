<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Part;
use App\Models\ProformaInvoice;
use App\Models\ProformaInvoiceItem;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\DB;

class ProformaService
{
    public function __construct(
        private SalesService $salesService,
        private CurrencyService $currencyService,
        private BranchScopeService $branchScope,
        private AuditService $audit,
    ) {}

    public function create(array $data, array $items): ProformaInvoice
    {
        return DB::transaction(function () use ($data, $items) {
            $this->branchScope->assertBranchAccess((int) $data['branch_id']);
            $data = $this->currencyService->applyToDocument($data);
            $customer = Customer::findOrFail($data['customer_id']);

            $proforma = ProformaInvoice::create($data);
            $this->syncItems($proforma, $items, $customer, $data['created_by'] ?? null);
            $this->recalculate($proforma);
            $this->audit->log('proforma.created', $proforma, null, $proforma->toArray(), $proforma->proforma_no);

            return $proforma->fresh(['items.part', 'customer', 'branch', 'currency']);
        });
    }

    public function convertToInvoice(ProformaInvoice $proforma, array $invoiceData, bool $postStock = false): SalesInvoice
    {
        return DB::transaction(function () use ($proforma, $invoiceData, $postStock) {
            if ($proforma->status === 'converted') {
                throw new \RuntimeException('Proforma already converted.');
            }

            $proforma->load('items');
            $items = $proforma->items->map(fn ($item) => [
                'part_id' => $item->part_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_percent' => $item->discount_percent,
                'vat_percent' => $item->vat_percent,
            ])->toArray();

            $data = array_merge([
                'branch_id' => $proforma->branch_id,
                'customer_id' => $proforma->customer_id,
                'proforma_invoice_id' => $proforma->id,
                'currency_id' => $proforma->currency_id,
                'exchange_rate' => $proforma->exchange_rate,
                'subtotal' => $proforma->subtotal,
                'discount_amount' => $proforma->discount_amount,
                'vat_amount' => $proforma->vat_amount,
                'total_amount' => $proforma->total_amount,
                'foreign_total' => $proforma->foreign_total,
                'remarks' => $proforma->remarks,
            ], $invoiceData);

            $invoice = $this->salesService->createInvoice($data, $items, $postStock);
            $proforma->update(['status' => 'converted', 'sales_invoice_id' => $invoice->id]);
            $this->audit->log('proforma.converted', $proforma, null, ['invoice_id' => $invoice->id], $proforma->proforma_no);

            return $invoice;
        });
    }

    protected function syncItems(ProformaInvoice $proforma, array $items, Customer $customer, ?int $userId): void
    {
        $proforma->items()->delete();

        foreach ($items as $row) {
            $line = $this->salesService->resolveLinePricing($row['part_id'], $customer->id, $userId);
            if (! empty($row['unit_price'])) {
                $line['unit_price'] = (float) $row['unit_price'];
            }

            $qty = (float) $row['quantity'];
            $disc = (float) ($row['discount_percent'] ?? $line['discount_percent']);
            $vat = (float) ($row['vat_percent'] ?? $line['vat_percent']);
            $sub = $qty * $line['unit_price'] * (1 - $disc / 100);
            $lineTotal = $sub * (1 + $vat / 100);

            ProformaInvoiceItem::create([
                'proforma_invoice_id' => $proforma->id,
                'part_id' => $row['part_id'],
                'quantity' => $qty,
                'unit_price' => $line['unit_price'],
                'discount_percent' => $disc,
                'vat_percent' => $vat,
                'line_total' => $lineTotal,
            ]);
        }
    }

    protected function recalculate(ProformaInvoice $proforma): void
    {
        $proforma->load('items');
        $subtotal = $proforma->items->sum(fn ($i) => (float) $i->quantity * (float) $i->unit_price);
        $total = $proforma->items->sum('line_total');

        $proforma->update([
            'subtotal' => $subtotal,
            'total_amount' => $total,
            'vat_amount' => $total - ($subtotal - (float) $proforma->discount_amount),
            'foreign_total' => $proforma->exchange_rate != 1 ? $total : null,
        ]);
    }
}
