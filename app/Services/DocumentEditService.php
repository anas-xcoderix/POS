<?php

namespace App\Services;

use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DocumentEditService
{
    public function __construct(
        private SalesService $salesService,
        private PurchaseService $purchaseService,
        private GranularPermissionService $permissions,
        private AuditService $audit,
        private FiscalPeriodService $fiscalPeriod,
    ) {}

    public function updatePostedSalesInvoice(User $user, SalesInvoice $invoice, array $data, array $items): SalesInvoice
    {
        $this->permissions->assert($user, 'sales.edit_posted');

        if ($invoice->status !== 'posted' || $invoice->voided_at) {
            throw new \RuntimeException('Only posted non-voided invoices can be edited.');
        }

        $this->fiscalPeriod->assertOpen($invoice->invoice_date);

        return DB::transaction(function () use ($user, $invoice, $data, $items) {
            $old = $invoice->toArray();

            $this->salesService->voidInvoice($invoice, 'Edit reversal', $user->id);
            $invoice->refresh();

            $invoice->update(array_merge($data, [
                'status' => 'draft',
                'voided_at' => null,
                'void_reason' => null,
            ]));

            $customer = $invoice->customer;
            $invoice->items()->delete();

            foreach ($items as $row) {
                $line = $this->salesService->resolveLinePricing($row['part_id'], $customer->id, $user->id);
                \App\Models\SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'part_id' => $row['part_id'],
                    'location_id' => $row['location_id'] ?? null,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'] ?? $line['unit_price'],
                    'discount_percent' => $row['discount_percent'] ?? $line['discount_percent'],
                    'vat_percent' => $row['vat_percent'] ?? $line['vat_percent'],
                    'line_total' => $line['line_total'],
                ]);
            }

            $this->salesService->postInvoice($invoice->fresh(), $user->id);
            $this->audit->logModelChange('sales.edited_posted', $invoice->fresh(), $old, $invoice->fresh()->toArray(), $invoice->invoice_no);

            return $invoice->fresh(['items.part', 'customer']);
        });
    }

    public function updatePostedPurchaseInvoice(User $user, PurchaseInvoice $invoice, array $data, array $items): PurchaseInvoice
    {
        $this->permissions->assert($user, 'purchase.edit_posted');

        if ($invoice->status !== 'posted' || $invoice->voided_at) {
            throw new \RuntimeException('Only posted non-voided purchase invoices can be edited.');
        }

        $this->fiscalPeriod->assertOpen($invoice->invoice_date);

        return DB::transaction(function () use ($user, $invoice, $data, $items) {
            $old = $invoice->toArray();
            $this->purchaseService->voidInvoice($invoice, 'Edit reversal', $user->id);

            $invoice->refresh()->update(array_merge($data, [
                'status' => 'draft',
                'voided_at' => null,
                'void_reason' => null,
            ]));

            $invoice->items()->delete();
            foreach ($items as $row) {
                \App\Models\PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'part_id' => $row['part_id'],
                    'location_id' => $row['location_id'] ?? null,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'line_total' => (float) $row['quantity'] * (float) $row['unit_price'],
                ]);
            }

            $this->purchaseService->postInvoice($invoice->fresh(), $user->id);
            $this->audit->logModelChange('purchase.edited_posted', $invoice->fresh(), $old, $invoice->fresh()->toArray(), $invoice->invoice_no);

            return $invoice->fresh(['items.part', 'vendor']);
        });
    }
}
