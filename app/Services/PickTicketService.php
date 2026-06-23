<?php

namespace App\Services;

use App\Models\PickTicket;
use App\Models\PickTicketItem;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\DB;

class PickTicketService
{
    public function __construct(
        private AuditService $audit,
        private BatchStockService $batchStock,
    ) {}

    public function createFromInvoice(SalesInvoice $invoice, ?int $userId = null): PickTicket
    {
        return DB::transaction(function () use ($invoice, $userId) {
            if ($invoice->status !== 'posted' && $invoice->status !== 'draft') {
                throw new \RuntimeException('Cannot create pick ticket for this invoice.');
            }

            $invoice->load('items.part');
            $pickNo = 'PK-'.now()->format('Ymd').'-'.str_pad((string) (PickTicket::count() + 1), 4, '0', STR_PAD_LEFT);

            $ticket = PickTicket::create([
                'pick_no' => $pickNo,
                'sales_invoice_id' => $invoice->id,
                'branch_id' => $invoice->branch_id,
                'location_id' => $invoice->items->first()?->location_id,
                'status' => 'open',
                'created_by' => $userId,
            ]);

            foreach ($invoice->items as $item) {
                if ($item->part?->part_number === 'SVC-LABOR') {
                    continue;
                }

                PickTicketItem::create([
                    'pick_ticket_id' => $ticket->id,
                    'sales_invoice_item_id' => $item->id,
                    'part_id' => $item->part_id,
                    'location_id' => $item->location_id,
                    'qty_ordered' => $item->quantity,
                    'qty_picked' => 0,
                ]);
            }

            $this->audit->log('pick.created', $ticket, null, $ticket->toArray(), $pickNo);

            return $ticket->fresh(['items.part', 'salesInvoice']);
        });
    }

    public function confirmPick(PickTicket $ticket, array $pickedQuantities, ?int $userId = null): PickTicket
    {
        return DB::transaction(function () use ($ticket, $pickedQuantities, $userId) {
            $ticket->load('items.part', 'salesInvoice');

            foreach ($ticket->items as $item) {
                $picked = (float) ($pickedQuantities[$item->id] ?? $item->qty_ordered);
                $item->update(['qty_picked' => $picked]);

                if ($picked > 0 && $item->location_id) {
                    $this->batchStock->issueFifo(
                        $ticket->branch_id,
                        $item->location_id,
                        $item->part_id,
                        $picked,
                        $ticket->sales_invoice_id,
                        $ticket->salesInvoice->invoice_no,
                        $userId
                    );
                }
            }

            $ticket->update([
                'status' => 'picked',
                'picked_at' => now(),
                'assigned_to' => $userId,
            ]);

            $this->audit->log('pick.confirmed', $ticket, null, ['status' => 'picked'], $ticket->pick_no);

            return $ticket->fresh(['items.part']);
        });
    }
}
