<?php

namespace App\Services;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(private StockService $stockService) {}

    public function createPurchaseOrder(array $data, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $items) {
            $order = PurchaseOrder::create($data);
            $this->syncOrderItems($order, $items);
            $this->recalculateOrder($order);

            return $order->fresh(['items.part', 'vendor', 'branch']);
        });
    }

    public function createPurchaseInvoice(array $data, array $items, bool $postStock = false): PurchaseInvoice
    {
        return DB::transaction(function () use ($data, $items, $postStock) {
            $invoice = PurchaseInvoice::create($data);
            $this->syncInvoiceItems($invoice, $items);
            $this->recalculateInvoice($invoice);

            if ($invoice->purchase_order_id) {
                $this->updatePoReceivedQuantities($invoice);
            }

            if ($postStock && $invoice->status === 'posted') {
                $this->postInvoiceStock($invoice, $data['created_by'] ?? null);
            }

            return $invoice->fresh(['items.part', 'vendor', 'branch', 'purchaseOrder']);
        });
    }

    public function createInvoiceFromPurchaseOrder(PurchaseOrder $order, array $invoiceData, ?array $receiveItems = null, bool $postStock = false): PurchaseInvoice
    {
        return DB::transaction(function () use ($order, $invoiceData, $receiveItems, $postStock) {
            $order->load('items.part');

            $items = [];
            foreach ($order->items as $poItem) {
                $pending = (float) $poItem->quantity - (float) $poItem->received_qty;
                if ($pending <= 0) {
                    continue;
                }

                $receiveQty = $pending;
                if ($receiveItems) {
                    $match = collect($receiveItems)->firstWhere('purchase_order_item_id', $poItem->id);
                    $receiveQty = $match ? min((float) $match['quantity'], $pending) : 0;
                }

                if ($receiveQty <= 0) {
                    continue;
                }

                $items[] = [
                    'part_id' => $poItem->part_id,
                    'location_id' => $invoiceData['default_location_id'] ?? null,
                    'quantity' => $receiveQty,
                    'unit_price' => $poItem->unit_price,
                    'purchase_order_item_id' => $poItem->id,
                ];
            }

            if (empty($items)) {
                throw new \RuntimeException('No pending quantities to receive on this PO.');
            }

            $data = array_merge([
                'branch_id' => $order->branch_id,
                'vendor_id' => $order->vendor_id,
                'purchase_order_id' => $order->id,
            ], $invoiceData);

            return $this->createPurchaseInvoice($data, $items, $postStock);
        });
    }

    public function postInvoice(PurchaseInvoice $invoice, ?int $userId = null): PurchaseInvoice
    {
        return DB::transaction(function () use ($invoice, $userId) {
            if ($invoice->status === 'posted') {
                return $invoice;
            }

            $invoice->load('items');
            $this->postInvoiceStock($invoice, $userId);
            $invoice->update(['status' => 'posted']);

            if ($invoice->purchase_order_id) {
                $this->updatePoReceivedQuantities($invoice);
                $this->updatePoStatus($invoice->purchase_order_id);
            }

            return $invoice->fresh();
        });
    }

    public function createStockTransfer(array $data, array $items): StockTransfer
    {
        return DB::transaction(function () use ($data, $items) {
            $transfer = StockTransfer::create($data);

            foreach ($items as $row) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'part_id' => $row['part_id'],
                    'from_location_id' => $row['from_location_id'],
                    'to_location_id' => $row['to_location_id'],
                    'quantity' => $row['quantity'],
                    'unit_cost' => $row['unit_cost'] ?? 0,
                ]);
            }

            return $transfer->fresh(['items.part', 'fromBranch', 'toBranch']);
        });
    }

    protected function updatePoReceivedQuantities(PurchaseInvoice $invoice): void
    {
        if (! $invoice->purchase_order_id) {
            return;
        }

        $invoice->load('items');
        $order = PurchaseOrder::with('items')->find($invoice->purchase_order_id);

        foreach ($invoice->items as $invItem) {
            $poItem = $order->items->firstWhere('part_id', $invItem->part_id);
            if ($poItem) {
                $poItem->increment('received_qty', (float) $invItem->quantity);
            }
        }
    }

    protected function updatePoStatus(int $purchaseOrderId): void
    {
        $order = PurchaseOrder::with('items')->find($purchaseOrderId);
        if (! $order) {
            return;
        }

        $fullyReceived = $order->items->every(fn ($i) => (float) $i->received_qty >= (float) $i->quantity);
        $partiallyReceived = $order->items->some(fn ($i) => (float) $i->received_qty > 0);

        if ($fullyReceived) {
            $order->update(['status' => 'received']);
        } elseif ($partiallyReceived) {
            $order->update(['status' => 'partial']);
        }
    }

    protected function postInvoiceStock(PurchaseInvoice $invoice, ?int $userId = null): void
    {
        foreach ($invoice->items as $item) {
            if (! $item->location_id) {
                throw new \RuntimeException('Location required for part: '.$item->part_id);
            }

            $this->stockService->receivePurchase(
                $invoice->branch_id,
                $item->location_id,
                $item->part_id,
                (float) $item->quantity,
                (float) $item->unit_price,
                $invoice->id,
                $invoice->invoice_no,
                $userId
            );
        }
    }

    protected function syncOrderItems(PurchaseOrder $order, array $items): void
    {
        $order->items()->delete();

        foreach ($items as $row) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'part_id' => $row['part_id'],
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'line_total' => (float) $row['quantity'] * (float) $row['unit_price'],
            ]);
        }
    }

    protected function syncInvoiceItems(PurchaseInvoice $invoice, array $items): void
    {
        $invoice->items()->delete();

        foreach ($items as $row) {
            PurchaseInvoiceItem::create([
                'purchase_invoice_id' => $invoice->id,
                'part_id' => $row['part_id'],
                'location_id' => $row['location_id'] ?? null,
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'line_total' => (float) $row['quantity'] * (float) $row['unit_price'],
            ]);
        }
    }

    protected function recalculateOrder(PurchaseOrder $order): void
    {
        $order->load('items');
        $subtotal = $order->items->sum('line_total');

        $order->update([
            'subtotal' => $subtotal,
            'total_amount' => $subtotal + (float) $order->vat_amount,
        ]);
    }

    protected function recalculateInvoice(PurchaseInvoice $invoice): void
    {
        $invoice->load('items');
        $subtotal = $invoice->items->sum('line_total');

        $invoice->update([
            'subtotal' => $subtotal,
            'total_amount' => $subtotal + (float) $invoice->vat_amount,
        ]);
    }
}
