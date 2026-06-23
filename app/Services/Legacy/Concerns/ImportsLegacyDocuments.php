<?php

namespace App\Services\Legacy\Concerns;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PaymentReceipt;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Vehicle;
use App\Models\JobCard;
use App\Models\Employee;
use App\Models\Department;

trait ImportsLegacyDocuments
{
    /** @param  array<int, object>  $detailRows */
    protected function upsertQuotation(object $header, array $detailRows, array $headerMap, array $detailMap, string $legacyKey): ?int
    {
        $no = $this->row->str($header, $headerMap['quotation_no'] ?? ['PickNo']) ?: 'Q-'.$legacyKey;
        $customerId = $this->resolveCustomerId($this->row->str($header, $headerMap['customer'] ?? []));
        if (! $customerId) {
            return null;
        }

        $quotation = Quotation::updateOrCreate(
            ['quotation_no' => $no],
            [
                'branch_id' => $this->resolveBranchId($this->row->str($header, $headerMap['branch'] ?? [])),
                'customer_id' => $customerId,
                'quotation_date' => $this->row->date($header, $headerMap['quotation_date'] ?? []) ?? now()->toDateString(),
                'valid_until' => $this->row->date($header, $headerMap['valid_until'] ?? []),
                'status' => $this->mapDocumentStatus($header, $headerMap['status'] ?? ['Posted']),
                'subtotal' => $this->row->decimal($header, $headerMap['subtotal'] ?? []),
                'discount_amount' => 0,
                'vat_amount' => $this->row->decimal($header, $headerMap['vat_amount'] ?? []),
                'total_amount' => $this->row->decimal($header, $headerMap['total_amount'] ?? []),
                'remarks' => $this->row->str($header, $headerMap['remarks'] ?? []),
            ]
        );

        $quotation->items()->delete();
        foreach ($detailRows as $line) {
            $partId = $this->resolvePartId($this->row->str($line, $detailMap['part'] ?? []));
            if (! $partId) {
                continue;
            }
            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'part_id' => $partId,
                'quantity' => $this->row->decimal($line, $detailMap['quantity'] ?? [], 1),
                'unit_price' => $this->row->decimal($line, $detailMap['unit_price'] ?? []),
                'discount_percent' => $this->row->decimal($line, $detailMap['discount_percent'] ?? []),
                'vat_percent' => $this->row->decimal($line, $detailMap['vat_percent'] ?? [], 15),
                'line_total' => $this->row->decimal($line, $detailMap['line_total'] ?? []),
            ]);
        }

        return $quotation->id;
    }

    /** @param  array<int, object>  $detailRows */
    protected function upsertSalesInvoice(object $header, array $detailRows, array $headerMap, array $detailMap, string $legacyKey): ?int
    {
        $no = $this->row->str($header, $headerMap['invoice_no'] ?? ['SInvNo']) ?: 'SI-'.$legacyKey;
        $customerId = $this->resolveCustomerId($this->row->str($header, $headerMap['customer'] ?? []));
        if (! $customerId) {
            return null;
        }

        $typeRaw = strtolower($this->row->str($header, $headerMap['invoice_type'] ?? [], 'cash') ?? 'cash');

        $invoice = SalesInvoice::updateOrCreate(
            ['invoice_no' => $no],
            [
                'branch_id' => $this->resolveBranchId($this->row->str($header, $headerMap['branch'] ?? [])),
                'customer_id' => $customerId,
                'invoice_date' => $this->row->date($header, $headerMap['invoice_date'] ?? []) ?? now()->toDateString(),
                'invoice_type' => str_contains($typeRaw, 'cred') ? 'credit' : 'cash',
                'status' => $this->mapDocumentStatus($header, $headerMap['status'] ?? ['Posted']),
                'subtotal' => $this->row->decimal($header, $headerMap['subtotal'] ?? []),
                'discount_amount' => $this->row->decimal($header, $headerMap['discount_amount'] ?? []),
                'vat_amount' => $this->row->decimal($header, $headerMap['vat_amount'] ?? []),
                'total_amount' => $this->row->decimal($header, $headerMap['total_amount'] ?? []),
                'paid_amount' => $this->row->decimal($header, $headerMap['paid_amount'] ?? []),
                'remarks' => $this->row->str($header, $headerMap['remarks'] ?? []),
            ]
        );

        $invoice->items()->delete();
        foreach ($detailRows as $line) {
            $partId = $this->resolvePartId($this->row->str($line, $detailMap['part'] ?? []));
            if (! $partId) {
                continue;
            }
            $locKey = $this->row->str($line, $detailMap['location'] ?? []);
            SalesInvoiceItem::create([
                'sales_invoice_id' => $invoice->id,
                'part_id' => $partId,
                'location_id' => $locKey ? ($this->maps->find('locations', $locKey) ?? $this->defaultLocation->id) : $this->defaultLocation->id,
                'quantity' => $this->row->decimal($line, $detailMap['quantity'] ?? [], 1),
                'unit_price' => $this->row->decimal($line, $detailMap['unit_price'] ?? []),
                'unit_cost' => $this->row->decimal($line, $detailMap['unit_cost'] ?? []),
                'discount_percent' => $this->row->decimal($line, $detailMap['discount_percent'] ?? []),
                'vat_percent' => $this->row->decimal($line, $detailMap['vat_percent'] ?? [], 15),
                'line_total' => $this->row->decimal($line, $detailMap['line_total'] ?? []),
            ]);
        }

        return $invoice->id;
    }

    /** @param  array<int, object>  $detailRows */
    protected function upsertSaleReturn(object $header, array $detailRows, array $headerMap, array $detailMap, string $legacyKey): ?int
    {
        $no = $this->row->str($header, $headerMap['return_no'] ?? ['RtnInvNo']) ?: 'SR-'.$legacyKey;
        $customerId = $this->resolveCustomerId($this->row->str($header, $headerMap['customer'] ?? []));
        if (! $customerId) {
            return null;
        }

        $invKey = $this->row->str($header, $headerMap['sales_invoice'] ?? []);
        $salesInvoiceId = $this->resolveSalesInvoiceId($invKey);

        $ret = SaleReturn::updateOrCreate(
            ['return_no' => $no],
            [
                'sales_invoice_id' => $salesInvoiceId,
                'branch_id' => $this->resolveBranchId($this->row->str($header, $headerMap['branch'] ?? [])),
                'customer_id' => $customerId,
                'return_date' => $this->row->date($header, $headerMap['return_date'] ?? []) ?? now()->toDateString(),
                'status' => $this->mapDocumentStatus($header, $headerMap['status'] ?? ['Posted']),
                'total_amount' => $this->row->decimal($header, $headerMap['total_amount'] ?? []),
                'remarks' => $this->row->str($header, $headerMap['remarks'] ?? []),
            ]
        );

        $ret->items()->delete();
        foreach ($detailRows as $line) {
            $partId = $this->resolvePartId($this->row->str($line, $detailMap['part'] ?? []));
            if (! $partId) {
                continue;
            }
            SaleReturnItem::create([
                'sale_return_id' => $ret->id,
                'part_id' => $partId,
                'location_id' => $this->defaultLocation->id,
                'quantity' => $this->row->decimal($line, $detailMap['quantity'] ?? [], 1),
                'unit_price' => $this->row->decimal($line, $detailMap['unit_price'] ?? []),
                'line_total' => $this->row->decimal($line, $detailMap['line_total'] ?? []),
            ]);
        }

        return $ret->id;
    }

    /** @param  array<int, object>  $detailRows */
    protected function upsertPurchaseOrder(object $header, array $detailRows, array $headerMap, array $detailMap, string $legacyKey): ?int
    {
        $no = $this->row->str($header, $headerMap['po_no'] ?? ['PONo']) ?: 'PO-'.$legacyKey;
        $vendorId = $this->resolveVendorId($this->row->str($header, $headerMap['vendor'] ?? []));
        if (! $vendorId) {
            return null;
        }

        $po = PurchaseOrder::updateOrCreate(
            ['po_no' => $no],
            [
                'branch_id' => $this->resolveBranchId($this->row->str($header, $headerMap['branch'] ?? [])),
                'vendor_id' => $vendorId,
                'po_date' => $this->row->date($header, $headerMap['po_date'] ?? []) ?? now()->toDateString(),
                'expected_date' => $this->row->date($header, $headerMap['expected_date'] ?? []),
                'status' => $this->mapDocumentStatus($header, $headerMap['status'] ?? ['Posted']),
                'subtotal' => $this->row->decimal($header, $headerMap['subtotal'] ?? []),
                'vat_amount' => $this->row->decimal($header, $headerMap['vat_amount'] ?? []),
                'total_amount' => $this->row->decimal($header, $headerMap['total_amount'] ?? []),
                'remarks' => $this->row->str($header, $headerMap['remarks'] ?? []),
            ]
        );

        $po->items()->delete();
        foreach ($detailRows as $line) {
            $partId = $this->resolvePartId($this->row->str($line, $detailMap['part'] ?? []));
            if (! $partId) {
                continue;
            }
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'part_id' => $partId,
                'quantity' => $this->row->decimal($line, $detailMap['quantity'] ?? [], 1),
                'unit_price' => $this->row->decimal($line, $detailMap['unit_price'] ?? []),
                'received_qty' => $this->row->decimal($line, $detailMap['received_qty'] ?? []),
                'line_total' => $this->row->decimal($line, $detailMap['line_total'] ?? []),
            ]);
        }

        return $po->id;
    }

    /** @param  array<int, object>  $detailRows */
    protected function upsertPurchaseInvoice(object $header, array $detailRows, array $headerMap, array $detailMap, string $legacyKey): ?int
    {
        $no = $this->row->str($header, $headerMap['invoice_no'] ?? ['InvNo']) ?: 'PI-'.$legacyKey;
        $vendorId = $this->resolveVendorId($this->row->str($header, $headerMap['vendor'] ?? []));
        if (! $vendorId) {
            return null;
        }

        $poKey = $this->row->str($header, $headerMap['purchase_order'] ?? []);
        $poId = $poKey ? $this->maps->find('purchase_orders', $poKey) : null;

        $pi = PurchaseInvoice::updateOrCreate(
            ['invoice_no' => $no],
            [
                'branch_id' => $this->resolveBranchId($this->row->str($header, $headerMap['branch'] ?? [])),
                'vendor_id' => $vendorId,
                'purchase_order_id' => $poId,
                'invoice_date' => $this->row->date($header, $headerMap['invoice_date'] ?? []) ?? now()->toDateString(),
                'vendor_invoice_no' => $this->row->str($header, $headerMap['vendor_invoice_no'] ?? []),
                'status' => $this->mapDocumentStatus($header, $headerMap['status'] ?? ['Posted']),
                'subtotal' => $this->row->decimal($header, $headerMap['subtotal'] ?? []),
                'vat_amount' => $this->row->decimal($header, $headerMap['vat_amount'] ?? []),
                'total_amount' => $this->row->decimal($header, $headerMap['total_amount'] ?? []),
                'paid_amount' => $this->row->decimal($header, $headerMap['paid_amount'] ?? []),
                'remarks' => $this->row->str($header, $headerMap['remarks'] ?? []),
            ]
        );

        $pi->items()->delete();
        foreach ($detailRows as $line) {
            $partId = $this->resolvePartId($this->row->str($line, $detailMap['part'] ?? []));
            if (! $partId) {
                continue;
            }
            $locKey = $this->row->str($line, $detailMap['location'] ?? []);
            PurchaseInvoiceItem::create([
                'purchase_invoice_id' => $pi->id,
                'part_id' => $partId,
                'location_id' => $locKey ? ($this->maps->find('locations', $locKey) ?? $this->defaultLocation->id) : $this->defaultLocation->id,
                'quantity' => $this->row->decimal($line, $detailMap['quantity'] ?? [], 1),
                'unit_price' => $this->row->decimal($line, $detailMap['unit_price'] ?? []),
                'line_total' => $this->row->decimal($line, $detailMap['line_total'] ?? []),
            ]);
        }

        return $pi->id;
    }

    /** @param  array<int, object>  $detailRows */
    protected function upsertJournalEntry(object $header, array $detailRows, array $headerMap, array $detailMap, string $legacyKey): ?int
    {
        $no = $this->row->str($header, $headerMap['entry_no'] ?? ['EntryNo']) ?: 'JE-'.$legacyKey;

        $entry = JournalEntry::updateOrCreate(
            ['entry_no' => $no],
            [
                'branch_id' => $this->resolveBranchId($this->row->str($header, $headerMap['branch'] ?? [])),
                'entry_date' => $this->row->date($header, $headerMap['entry_date'] ?? []) ?? now()->toDateString(),
                'description' => $this->row->str($header, $headerMap['description'] ?? []) ?: 'Legacy import '.$legacyKey,
                'status' => 'posted',
                'entry_type' => 'legacy',
            ]
        );

        $entry->lines()->delete();
        foreach ($detailRows as $line) {
            $accKey = $this->row->str($line, $detailMap['account'] ?? []);
            $accountId = $accKey ? ($this->maps->find('accounts', $accKey) ?? Account::where('account_code', $accKey)->value('id')) : null;
            if (! $accountId) {
                continue;
            }
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $accountId,
                'debit' => $this->row->decimal($line, $detailMap['debit'] ?? []),
                'credit' => $this->row->decimal($line, $detailMap['credit'] ?? []),
                'description' => $this->row->str($line, $detailMap['description'] ?? []),
            ]);
        }

        return $entry->id;
    }

    /** @param  array<string, mixed>  $headerMap */
    protected function upsertPaymentReceipt(object $header, array $headerMap, string $legacyKey): ?int
    {
        $no = $this->row->str($header, $headerMap['receipt_no'] ?? ['ReceiptNo']) ?: 'RC-'.$legacyKey;
        $partyType = strtolower($this->row->str($header, $headerMap['party_type'] ?? [], 'customer') ?? 'customer');
        $custKey = $this->row->str($header, $headerMap['customer'] ?? []);
        $vendKey = $this->row->str($header, $headerMap['vendor'] ?? []);

        $receipt = PaymentReceipt::updateOrCreate(
            ['receipt_no' => $no],
            [
                'party_type' => str_contains($partyType, 'vend') ? 'vendor' : 'customer',
                'customer_id' => $custKey ? $this->resolveCustomerId($custKey) : null,
                'vendor_id' => $vendKey ? $this->resolveVendorId($vendKey) : null,
                'branch_id' => $this->resolveBranchId($this->row->str($header, $headerMap['branch'] ?? [])),
                'receipt_date' => $this->row->date($header, $headerMap['receipt_date'] ?? []) ?? now()->toDateString(),
                'payment_method' => strtolower($this->row->str($header, $headerMap['payment_method'] ?? [], 'cash') ?? 'cash'),
                'amount' => $this->row->decimal($header, $headerMap['amount'] ?? []),
                'reference_no' => $this->row->str($header, $headerMap['reference_no'] ?? []),
                'sales_invoice_id' => $this->resolveSalesInvoiceId($this->row->str($header, $headerMap['sales_invoice'] ?? [])),
                'status' => 'posted',
                'remarks' => $this->row->str($header, $headerMap['remarks'] ?? []),
            ]
        );

        return $receipt->id;
    }

    /** @param  array<int, object>  $detailRows */
    protected function upsertStockTransfer(object $header, array $detailRows, array $headerMap, array $detailMap, string $legacyKey): ?int
    {
        $no = $this->row->str($header, $headerMap['transfer_no'] ?? ['TransferNo']) ?: 'TR-'.$legacyKey;

        $transfer = StockTransfer::updateOrCreate(
            ['transfer_no' => $no],
            [
                'from_branch_id' => $this->resolveBranchId($this->row->str($header, $headerMap['from_branch'] ?? [])),
                'to_branch_id' => $this->resolveBranchId($this->row->str($header, $headerMap['to_branch'] ?? [])),
                'transfer_date' => $this->row->date($header, $headerMap['transfer_date'] ?? []) ?? now()->toDateString(),
                'status' => $this->mapDocumentStatus($header, $headerMap['status'] ?? ['Posted']),
                'remarks' => $this->row->str($header, $headerMap['remarks'] ?? []),
            ]
        );

        $transfer->items()->delete();
        foreach ($detailRows as $line) {
            $partId = $this->resolvePartId($this->row->str($line, $detailMap['part'] ?? []));
            if (! $partId) {
                continue;
            }
            StockTransferItem::create([
                'stock_transfer_id' => $transfer->id,
                'part_id' => $partId,
                'from_location_id' => $this->defaultLocation->id,
                'to_location_id' => $this->defaultLocation->id,
                'quantity' => $this->row->decimal($line, $detailMap['quantity'] ?? [], 1),
                'unit_cost' => $this->row->decimal($line, $detailMap['unit_cost'] ?? []),
            ]);
        }

        return $transfer->id;
    }

    /** @param  array<string, mixed>  $map */
    protected function insertStockMovement(object $row, array $map, string $legacyKey): ?int
    {
        $partId = $this->resolvePartId($this->row->str($row, $map['part'] ?? []));
        if (! $partId) {
            return null;
        }

        $movement = StockMovement::create([
            'branch_id' => $this->resolveBranchId($this->row->str($row, $map['branch'] ?? [])),
            'location_id' => $this->defaultLocation->id,
            'part_id' => $partId,
            'movement_type' => $this->row->str($row, $map['movement_type'] ?? [], 'legacy') ?? 'legacy',
            'reference_no' => $this->row->str($row, $map['reference_no'] ?? []) ?: $legacyKey,
            'quantity_in' => $this->row->decimal($row, $map['quantity_in'] ?? []),
            'quantity_out' => $this->row->decimal($row, $map['quantity_out'] ?? []),
            'unit_cost' => $this->row->decimal($row, $map['unit_cost'] ?? []),
            'balance_after' => 0,
            'remarks' => $this->row->str($row, $map['remarks'] ?? []),
            'movement_date' => $this->row->date($row, $map['movement_date'] ?? []) ?? now()->toDateString(),
        ]);

        return $movement->id;
    }

    /** @param  array<string, mixed>  $map */
    protected function upsertVehicle(object $row, array $map, string $legacyKey): ?int
    {
        $plate = $this->row->str($row, $map['plate_no'] ?? ['PlateNo']) ?: $legacyKey;

        return Vehicle::updateOrCreate(
            ['plate_no' => $plate],
            [
                'customer_id' => $this->resolveCustomerId($this->row->str($row, $map['customer'] ?? [])),
                'make' => $this->row->str($row, $map['make'] ?? []),
                'model' => $this->row->str($row, $map['model'] ?? []),
                'year' => $this->row->str($row, $map['year'] ?? []),
                'vin' => $this->row->str($row, $map['vin'] ?? []),
                'color' => $this->row->str($row, $map['color'] ?? []),
                'istimara_expiry' => $this->row->date($row, $map['istimara_expiry'] ?? []),
                'remarks' => $this->row->str($row, $map['remarks'] ?? []),
                'is_active' => true,
            ]
        )->id;
    }

    /** @param  array<string, mixed>  $map */
    protected function upsertJobCard(object $row, array $map, string $legacyKey): ?int
    {
        $no = $this->row->str($row, $map['job_no'] ?? ['JobNo']) ?: 'JC-'.$legacyKey;
        $customerId = $this->resolveCustomerId($this->row->str($row, $map['customer'] ?? []));
        if (! $customerId) {
            return null;
        }

        $vehicleKey = $this->row->str($row, $map['vehicle'] ?? []);
        $vehicleId = $vehicleKey ? ($this->maps->find('vehicles', $vehicleKey) ?? Vehicle::where('plate_no', $vehicleKey)->value('id')) : null;

        return JobCard::updateOrCreate(
            ['job_no' => $no],
            [
                'branch_id' => $this->resolveBranchId($this->row->str($row, $map['branch'] ?? [])),
                'customer_id' => $customerId,
                'vehicle_id' => $vehicleId,
                'job_date' => $this->row->date($row, $map['job_date'] ?? []) ?? now()->toDateString(),
                'promised_date' => $this->row->date($row, $map['promised_date'] ?? []),
                'status' => strtolower($this->row->str($row, $map['status'] ?? [], 'open') ?? 'open'),
                'labor_total' => $this->row->decimal($row, $map['labor_total'] ?? []),
                'parts_total' => $this->row->decimal($row, $map['parts_total'] ?? []),
                'total_amount' => $this->row->decimal($row, $map['total_amount'] ?? []),
                'mechanic_id' => ($m = $this->row->str($row, $map['mechanic'] ?? [])) ? $this->maps->find('employees', $m) : null,
                'complaint' => $this->row->str($row, $map['complaint'] ?? []),
                'remarks' => $this->row->str($row, $map['remarks'] ?? []),
            ]
        )->id;
    }

    /** @param  array<string, mixed>  $map */
    protected function upsertEmployee(object $row, array $map, string $legacyKey): ?int
    {
        $empNo = $this->row->str($row, $map['employee_no'] ?? ['EmployeeNo']) ?: $legacyKey;
        $deptKey = $this->row->str($row, $map['department'] ?? []);

        return Employee::updateOrCreate(
            ['employee_no' => $empNo],
            [
                'branch_id' => $this->resolveBranchId($this->row->str($row, $map['branch'] ?? [])),
                'department_id' => $deptKey ? ($this->maps->find('departments', $deptKey) ?? Department::value('id')) : Department::value('id'),
                'name' => $this->row->str($row, $map['name'] ?? ['Name']) ?: $empNo,
                'name_ar' => $this->row->str($row, $map['name_ar'] ?? []),
                'phone' => $this->row->str($row, $map['phone'] ?? []),
                'email' => $this->row->str($row, $map['email'] ?? []),
                'hire_date' => $this->row->date($row, $map['hire_date'] ?? []),
                'job_title' => $this->row->str($row, $map['job_title'] ?? []),
                'basic_salary' => $this->row->decimal($row, $map['basic_salary'] ?? []),
                'housing_allowance' => $this->row->decimal($row, $map['housing_allowance'] ?? []),
                'transport_allowance' => $this->row->decimal($row, $map['transport_allowance'] ?? []),
                'aqama_no' => $this->row->str($row, $map['aqama_no'] ?? []),
                'aqama_expiry' => $this->row->date($row, $map['aqama_expiry'] ?? []),
                'license_no' => $this->row->str($row, $map['license_no'] ?? []),
                'license_expiry' => $this->row->date($row, $map['license_expiry'] ?? []),
                'is_active' => true,
            ]
        )->id;
    }
}
