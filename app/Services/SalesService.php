<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Part;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use Illuminate\Support\Facades\DB;

class SalesService
{
    public function __construct(
        private StockService $stockService,
        private PricingService $pricingService,
        private TaxService $taxService,
        private CreditService $creditService,
        private BranchScopeService $branchScope,
        private AccountingService $accountingService,
        private FiscalPeriodService $fiscalPeriod,
    ) {}

    public function createQuotation(array $data, array $items): Quotation
    {
        return DB::transaction(function () use ($data, $items) {
            $this->branchScope->assertBranchAccess((int) $data['branch_id']);
            $customer = Customer::findOrFail($data['customer_id']);
            $quotation = Quotation::create($data);
            $this->syncQuotationItems($quotation, $items, $customer, $data['created_by'] ?? null);
            $this->recalculateQuotation($quotation);

            return $quotation->fresh(['items.part', 'customer', 'branch']);
        });
    }

    public function convertQuotationToInvoice(Quotation $quotation, array $invoiceData, bool $postStock = false): SalesInvoice
    {
        return DB::transaction(function () use ($quotation, $invoiceData, $postStock) {
            $quotation->load('items');

            if ($quotation->status === 'converted') {
                throw new \RuntimeException('Quotation already converted.');
            }

            $items = $quotation->items->map(fn ($item) => [
                'part_id' => $item->part_id,
                'location_id' => $invoiceData['default_location_id'] ?? null,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_percent' => $item->discount_percent,
                'vat_percent' => $item->vat_percent,
            ])->toArray();

            $data = array_merge([
                'branch_id' => $quotation->branch_id,
                'customer_id' => $quotation->customer_id,
                'quotation_id' => $quotation->id,
                'subtotal' => $quotation->subtotal,
                'discount_amount' => $quotation->discount_amount,
                'vat_amount' => $quotation->vat_amount,
                'total_amount' => $quotation->total_amount,
                'remarks' => $quotation->remarks,
            ], $invoiceData);

            $invoice = $this->createInvoice($data, $items, $postStock);
            $quotation->update(['status' => 'converted']);

            return $invoice;
        });
    }

    public function createInvoice(array $data, array $items, bool $postStock = false): SalesInvoice
    {
        return DB::transaction(function () use ($data, $items, $postStock) {
            $this->branchScope->assertBranchAccess((int) $data['branch_id']);
            $customer = Customer::findOrFail($data['customer_id']);

            $invoice = SalesInvoice::create($data);
            $this->syncInvoiceItems($invoice, $items, $customer, $data['created_by'] ?? null);
            $this->recalculateInvoice($invoice);

            if (($data['invoice_type'] ?? 'cash') === 'credit') {
                $invoice->refresh();
                $this->creditService->assertCanCharge(
                    $customer->fresh(),
                    (float) $invoice->total_amount - (float) ($data['paid_amount'] ?? 0),
                    'credit'
                );
            }

            if ($postStock && $invoice->status === 'posted') {
                $this->postInvoiceStock($invoice, $data['created_by'] ?? null);
                $invoice->refresh();
                $this->creditService->chargeForInvoice($invoice);
                $this->fiscalPeriod->assertOpen($invoice->invoice_date);
                $this->accountingService->postSalesInvoice($invoice->fresh(['items.part', 'customer']), $data['created_by'] ?? null);
            } elseif ($invoice->status === 'draft') {
                $this->reserveInvoiceStock($invoice);
            }

            return $invoice->fresh(['items.part', 'customer', 'branch']);
        });
    }

    public function createSaleReturn(array $data, array $items, bool $postStock = false): SaleReturn
    {
        return DB::transaction(function () use ($data, $items, $postStock) {
            $this->branchScope->assertBranchAccess((int) $data['branch_id']);
            $return = SaleReturn::create($data);
            $this->syncReturnItems($return, $items);
            $this->recalculateReturn($return);

            if ($postStock && $return->status === 'posted') {
                $this->postReturnStock($return, $data['created_by'] ?? null);
                $return->refresh();
                $this->creditService->creditForReturn($return);
                $this->accountingService->postSaleReturn($return->fresh(['items.part', 'salesInvoice']), $data['created_by'] ?? null);
            }

            return $return->fresh(['items.part', 'customer', 'branch', 'salesInvoice']);
        });
    }

    public function postSaleReturn(SaleReturn $return, ?int $userId = null): SaleReturn
    {
        return DB::transaction(function () use ($return, $userId) {
            if ($return->status === 'posted') {
                return $return;
            }

            $return->load('items');
            $this->postReturnStock($return, $userId);
            $return->update(['status' => 'posted']);
            $return->refresh();
            $this->creditService->creditForReturn($return);
            $this->accountingService->postSaleReturn($return->fresh(['items.part', 'salesInvoice']), $userId);

            return $return->fresh();
        });
    }

    public function postInvoice(SalesInvoice $invoice, ?int $userId = null): SalesInvoice
    {
        return DB::transaction(function () use ($invoice, $userId) {
            if ($invoice->status === 'posted') {
                return $invoice;
            }

            $this->fiscalPeriod->assertOpen($invoice->invoice_date);
            $invoice->load(['items', 'customer']);
            $this->creditService->assertCanCharge(
                $invoice->customer,
                (float) $invoice->total_amount - (float) $invoice->paid_amount,
                $invoice->invoice_type
            );

            $this->postInvoiceStock($invoice, $userId, true);
            $invoice->update(['status' => 'posted']);
            $invoice->refresh();
            $this->creditService->chargeForInvoice($invoice);
            $this->accountingService->postSalesInvoice($invoice->fresh(['items.part', 'customer']), $userId);

            return $invoice->fresh();
        });
    }

    public function voidInvoice(SalesInvoice $invoice, string $reason, ?int $userId = null): SalesInvoice
    {
        return DB::transaction(function () use ($invoice, $reason, $userId) {
            if ($invoice->voided_at) {
                throw new \RuntimeException('Invoice already voided.');
            }

            $this->fiscalPeriod->assertOpen($invoice->invoice_date);
            $invoice->load('items');

            if ($invoice->status === 'draft') {
                $this->releaseInvoiceReservations($invoice);
            } elseif ($invoice->status === 'posted') {
                foreach ($invoice->items as $item) {
                    $item->loadMissing('part');
                    if ($item->part?->part_number === 'SVC-LABOR' || ! $item->location_id) {
                        continue;
                    }
                    $this->stockService->receiveSaleReturn(
                        $invoice->branch_id,
                        $item->location_id,
                        $item->part_id,
                        (float) $item->quantity,
                        (float) ($item->unit_cost ?: $item->part?->cost_price ?? 0),
                        0,
                        'VOID-'.$invoice->invoice_no,
                        $userId
                    );
                }
                $this->accountingService->reverseDocumentEntry(SalesInvoice::class, $invoice->id, 'Void '.$invoice->invoice_no, $userId);
            } else {
                throw new \RuntimeException('Cannot void this invoice.');
            }

            $invoice->update([
                'voided_at' => now(),
                'void_reason' => $reason,
                'status' => 'voided',
            ]);

            return $invoice->fresh();
        });
    }

    public function resolveLinePricing(int $partId, ?int $customerId, ?int $userId = null): array
    {
        $part = Part::findOrFail($partId);
        $customer = $customerId ? Customer::find($customerId) : null;
        $pricing = $this->pricingService->resolveUnitPrice($part, $customer, $userId);
        $tax = $this->taxService->calculateLine(1, $pricing['unit_price'], $pricing['discount_percent'], null, $part);

        return array_merge($pricing, [
            'vat_percent' => $tax['vat_percent'],
            'line_total' => $tax['line_total'],
            'part_number' => $part->part_number,
            'description' => $part->description_en,
        ]);
    }

    protected function postReturnStock(SaleReturn $return, ?int $userId = null): void
    {
        foreach ($return->items as $item) {
            if (! $item->location_id) {
                throw new \RuntimeException('Location required for return line.');
            }

            $this->stockService->receiveSaleReturn(
                $return->branch_id,
                $item->location_id,
                $item->part_id,
                (float) $item->quantity,
                (float) $item->unit_price,
                $return->id,
                $return->return_no,
                $userId
            );
        }
    }

    protected function postInvoiceStock(SalesInvoice $invoice, ?int $userId = null, bool $fromReservation = false): void
    {
        foreach ($invoice->items as $item) {
            $item->loadMissing('part');
            if ($item->part?->part_number === 'SVC-LABOR') {
                continue;
            }

            if (! $item->location_id) {
                throw new \RuntimeException('Location required for part: '.$item->part_id);
            }

            $this->stockService->issueForSale(
                $invoice->branch_id,
                $item->location_id,
                $item->part_id,
                (float) $item->quantity,
                $invoice->id,
                $invoice->invoice_no,
                $userId,
                $fromReservation
            );
        }
    }

    protected function reserveInvoiceStock(SalesInvoice $invoice): void
    {
        foreach ($invoice->items as $item) {
            $item->loadMissing('part');
            if ($item->part?->part_number === 'SVC-LABOR' || ! $item->location_id) {
                continue;
            }

            $this->stockService->reserveForSale(
                $invoice->branch_id,
                $item->location_id,
                $item->part_id,
                (float) $item->quantity,
                $invoice->id,
                $invoice->invoice_no
            );
        }
    }

    protected function releaseInvoiceReservations(SalesInvoice $invoice): void
    {
        foreach ($invoice->items as $item) {
            $item->loadMissing('part');
            if ($item->part?->part_number === 'SVC-LABOR' || ! $item->location_id) {
                continue;
            }

            $this->stockService->releaseReservation(
                $invoice->branch_id,
                $item->location_id,
                $item->part_id,
                (float) $item->quantity,
                $invoice->id,
                $invoice->invoice_no
            );
        }
    }

    protected function syncReturnItems(SaleReturn $return, array $items): void
    {
        $return->items()->delete();

        foreach ($items as $row) {
            SaleReturnItem::create([
                'sale_return_id' => $return->id,
                'part_id' => $row['part_id'],
                'location_id' => $row['location_id'] ?? null,
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'line_total' => (float) $row['quantity'] * (float) $row['unit_price'],
            ]);
        }
    }

    protected function recalculateReturn(SaleReturn $return): void
    {
        $return->load('items');
        $return->update(['total_amount' => $return->items->sum('line_total')]);
    }

    protected function syncQuotationItems(Quotation $quotation, array $items, Customer $customer, ?int $userId): void
    {
        $quotation->items()->delete();

        foreach ($items as $row) {
            $line = $this->buildSalesLine($row, $customer, $userId);
            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'part_id' => $row['part_id'],
                'quantity' => $row['quantity'],
                'unit_price' => $line['unit_price'],
                'discount_percent' => $line['discount_percent'],
                'vat_percent' => $line['vat_percent'],
                'line_total' => $line['line_total'],
            ]);
        }
    }

    protected function syncInvoiceItems(SalesInvoice $invoice, array $items, Customer $customer, ?int $userId): void
    {
        $invoice->items()->delete();

        foreach ($items as $row) {
            $line = $this->buildSalesLine($row, $customer, $userId);
            SalesInvoiceItem::create([
                'sales_invoice_id' => $invoice->id,
                'part_id' => $row['part_id'],
                'location_id' => $row['location_id'] ?? null,
                'quantity' => $row['quantity'],
                'unit_price' => $line['unit_price'],
                'unit_cost' => $row['unit_cost'] ?? 0,
                'discount_percent' => $line['discount_percent'],
                'vat_percent' => $line['vat_percent'],
                'line_total' => $line['line_total'],
            ]);
        }
    }

    protected function buildSalesLine(array $row, Customer $customer, ?int $userId): array
    {
        $part = Part::findOrFail($row['part_id']);
        $quantity = (float) $row['quantity'];

        if (! empty($row['manual_price'])) {
            $unitPrice = (float) $row['unit_price'];
            $discountPercent = (float) ($row['discount_percent'] ?? 0);
            $discountPercent = $this->pricingService->capDiscountForUser($discountPercent, $userId);
            $vatPercent = (float) ($row['vat_percent'] ?? $this->taxService->resolveVatPercent($part));
        } else {
            $pricing = $this->pricingService->resolveUnitPrice($part, $customer, $userId);
            $unitPrice = isset($row['unit_price']) && (float) $row['unit_price'] > 0
                ? (float) $row['unit_price']
                : $pricing['unit_price'];
            $discountPercent = (float) ($row['discount_percent'] ?? $pricing['discount_percent']);
            $discountPercent = $this->pricingService->capDiscountForUser($discountPercent, $userId);
            $vatPercent = (float) ($row['vat_percent'] ?? $this->taxService->resolveVatPercent($part));
        }

        $tax = $this->taxService->calculateLine($quantity, $unitPrice, $discountPercent, $vatPercent, $part);

        return [
            'unit_price' => $unitPrice,
            'discount_percent' => $discountPercent,
            'vat_percent' => $tax['vat_percent'],
            'line_total' => $tax['line_total'],
        ];
    }

    protected function recalculateQuotation(Quotation $quotation): void
    {
        $quotation->load('items');
        $subtotal = $quotation->items->sum(fn ($i) => (float) $i->quantity * (float) $i->unit_price);
        $total = $quotation->items->sum('line_total');

        $quotation->update([
            'subtotal' => $subtotal,
            'total_amount' => $total,
            'vat_amount' => $total - ($subtotal - (float) $quotation->discount_amount),
        ]);
    }

    protected function recalculateInvoice(SalesInvoice $invoice): void
    {
        $invoice->load('items');
        $subtotal = $invoice->items->sum(fn ($i) => (float) $i->quantity * (float) $i->unit_price);
        $total = $invoice->items->sum('line_total');

        $invoice->update([
            'subtotal' => $subtotal,
            'total_amount' => $total,
            'vat_amount' => $total - ($subtotal - (float) $invoice->discount_amount),
        ]);
    }
}
