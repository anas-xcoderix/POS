<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use Illuminate\Support\Facades\DB;

class SalesService
{
    public function __construct(private StockService $stockService) {}

    public function createQuotation(array $data, array $items): Quotation
    {
        return DB::transaction(function () use ($data, $items) {
            $quotation = Quotation::create($data);
            $this->syncQuotationItems($quotation, $items);
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
            $invoice = SalesInvoice::create($data);
            $this->syncInvoiceItems($invoice, $items);
            $this->recalculateInvoice($invoice);

            if ($postStock && $invoice->status === 'posted') {
                $this->postInvoiceStock($invoice, $data['created_by'] ?? null);
            }

            return $invoice->fresh(['items.part', 'customer', 'branch']);
        });
    }

    public function createSaleReturn(array $data, array $items, bool $postStock = false): SaleReturn
    {
        return DB::transaction(function () use ($data, $items, $postStock) {
            $return = SaleReturn::create($data);
            $this->syncReturnItems($return, $items);
            $this->recalculateReturn($return);

            if ($postStock && $return->status === 'posted') {
                $this->postReturnStock($return, $data['created_by'] ?? null);
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

            return $return->fresh();
        });
    }

    public function postInvoice(SalesInvoice $invoice, ?int $userId = null): SalesInvoice
    {
        return DB::transaction(function () use ($invoice, $userId) {
            if ($invoice->status === 'posted') {
                return $invoice;
            }

            $invoice->load('items');
            $this->postInvoiceStock($invoice, $userId);
            $invoice->update(['status' => 'posted']);

            return $invoice->fresh();
        });
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

    protected function postInvoiceStock(SalesInvoice $invoice, ?int $userId = null): void
    {
        foreach ($invoice->items as $item) {
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
                $userId
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

    protected function syncQuotationItems(Quotation $quotation, array $items): void
    {
        $quotation->items()->delete();

        foreach ($items as $row) {
            $lineTotal = (float) $row['quantity'] * (float) $row['unit_price'];
            $discount = $lineTotal * ((float) ($row['discount_percent'] ?? 0) / 100);
            $net = $lineTotal - $discount;
            $vat = $net * ((float) ($row['vat_percent'] ?? 0) / 100);

            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'part_id' => $row['part_id'],
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'discount_percent' => $row['discount_percent'] ?? 0,
                'vat_percent' => $row['vat_percent'] ?? 0,
                'line_total' => $net + $vat,
            ]);
        }
    }

    protected function syncInvoiceItems(SalesInvoice $invoice, array $items): void
    {
        $invoice->items()->delete();

        foreach ($items as $row) {
            $lineTotal = (float) $row['quantity'] * (float) $row['unit_price'];
            $discount = $lineTotal * ((float) ($row['discount_percent'] ?? 0) / 100);
            $net = $lineTotal - $discount;
            $vat = $net * ((float) ($row['vat_percent'] ?? 0) / 100);

            SalesInvoiceItem::create([
                'sales_invoice_id' => $invoice->id,
                'part_id' => $row['part_id'],
                'location_id' => $row['location_id'] ?? null,
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'unit_cost' => $row['unit_cost'] ?? 0,
                'discount_percent' => $row['discount_percent'] ?? 0,
                'vat_percent' => $row['vat_percent'] ?? 0,
                'line_total' => $net + $vat,
            ]);
        }
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
