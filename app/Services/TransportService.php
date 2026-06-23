<?php

namespace App\Services;

use App\Models\Account;
use App\Models\DeliveryNote;
use App\Models\TransportCashVoucher;
use App\Models\TransportCashVoucherItem;
use App\Models\TransportDriver;
use App\Models\TransportShipment;
use Illuminate\Support\Facades\DB;

class TransportService
{
    public function __construct(
        private AuditService $audit,
        private CashBookService $cashBook,
    ) {}

    public function nextShipmentNo(): string
    {
        return 'SH-'.now()->format('Ymd').'-'.str_pad((string) (TransportShipment::count() + 1), 4, '0', STR_PAD_LEFT);
    }

    public function nextVoucherNo(): string
    {
        return 'TV-'.now()->format('Ymd').'-'.str_pad((string) (TransportCashVoucher::count() + 1), 4, '0', STR_PAD_LEFT);
    }

    public function createShipment(array $data): TransportShipment
    {
        return DB::transaction(function () use ($data) {
            $shipment = TransportShipment::create($data);
            $this->audit->log('transport.shipment.created', $shipment, null, $shipment->toArray(), $shipment->shipment_no);

            return $shipment;
        });
    }

    public function createFromDeliveryNote(DeliveryNote $note, ?int $driverId = null): TransportShipment
    {
        $driver = $driverId ? TransportDriver::find($driverId) : null;

        return $this->createShipment([
            'shipment_no' => $this->nextShipmentNo(),
            'branch_id' => $note->branch_id,
            'customer_id' => $note->customer_id,
            'transport_driver_id' => $driverId,
            'delivery_note_id' => $note->id,
            'sales_invoice_id' => $note->sales_invoice_id,
            'ship_date' => $note->delivery_date ?? now()->toDateString(),
            'expected_date' => $note->delivery_date,
            'status' => 'dispatched',
            'vehicle_plate' => $driver?->vehicle_plate ?? $note->vehicle_plate,
            'ship_to_address' => $note->customer?->address,
            'contact_phone' => $note->customer?->phone,
            'remarks' => $note->remarks,
            'created_by' => auth()->id(),
            'dispatched_at' => now(),
        ]);
    }

    public function updateStatus(TransportShipment $shipment, string $status): TransportShipment
    {
        if (! in_array($status, TransportShipment::STATUSES, true)) {
            throw new \InvalidArgumentException('Invalid shipment status.');
        }

        return DB::transaction(function () use ($shipment, $status) {
            $before = $shipment->status;
            $updates = ['status' => $status];

            if ($status === 'dispatched' && ! $shipment->dispatched_at) {
                $updates['dispatched_at'] = now();
            }

            if ($status === 'delivered') {
                $updates['delivered_at'] = now();
            }

            $shipment->update($updates);
            $this->audit->log('transport.shipment.status', $shipment, ['status' => $before], ['status' => $status], $shipment->shipment_no);

            return $shipment->fresh();
        });
    }

    public function createCashVoucher(array $data, array $shipmentAmounts): TransportCashVoucher
    {
        return DB::transaction(function () use ($data, $shipmentAmounts) {
            $total = collect($shipmentAmounts)->sum(fn ($row) => (float) ($row['amount'] ?? 0));

            $voucher = TransportCashVoucher::create(array_merge($data, [
                'total_amount' => $total,
                'status' => 'draft',
            ]));

            foreach ($shipmentAmounts as $row) {
                $shipment = TransportShipment::lockForUpdate()->findOrFail($row['shipment_id']);
                $amount = (float) $row['amount'];

                if ($amount <= 0) {
                    continue;
                }

                if ($amount > $shipment->codOutstanding()) {
                    throw new \RuntimeException("Amount exceeds COD outstanding for {$shipment->shipment_no}.");
                }

                TransportCashVoucherItem::create([
                    'transport_cash_voucher_id' => $voucher->id,
                    'transport_shipment_id' => $shipment->id,
                    'amount' => $amount,
                ]);
            }

            $this->audit->log('transport.voucher.created', $voucher, null, $voucher->toArray(), $voucher->voucher_no);

            return $voucher->load(['items.shipment', 'driver']);
        });
    }

    public function postCashVoucher(TransportCashVoucher $voucher, int $userId): TransportCashVoucher
    {
        if ($voucher->status === 'posted') {
            throw new \RuntimeException('Voucher already posted.');
        }

        return DB::transaction(function () use ($voucher, $userId) {
            $voucher->load('items.shipment', 'driver');

            $cashAccountId = Account::where('code', config('erp.gl_accounts.cash'))->value('id');
            if (! $cashAccountId) {
                throw new \RuntimeException('Cash GL account not configured.');
            }

            $entry = $this->cashBook->recordEntry([
                'branch_id' => $voucher->branch_id,
                'entry_date' => $voucher->voucher_date->toDateString(),
                'entry_type' => 'receipt',
                'account_id' => $cashAccountId,
                'amount' => $voucher->total_amount,
                'description' => 'Transport cash — '.$voucher->driver?->name.' ('.$voucher->voucher_no.')',
                'reference_no' => $voucher->voucher_no,
                'created_by' => $userId,
            ]);

            foreach ($voucher->items as $item) {
                $shipment = $item->shipment;
                $newCollected = (float) $shipment->cod_collected + (float) $item->amount;
                $shipment->update([
                    'cod_collected' => $newCollected,
                    'cod_settled' => $newCollected >= (float) $shipment->cod_amount && (float) $shipment->cod_amount > 0,
                ]);
            }

            $voucher->update([
                'status' => 'posted',
                'cash_book_entry_id' => $entry->id,
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            $this->audit->log('transport.voucher.posted', $voucher, null, $voucher->fresh()->toArray(), $voucher->voucher_no);

            return $voucher->fresh(['items.shipment', 'driver', 'cashBookEntry']);
        });
    }
}
