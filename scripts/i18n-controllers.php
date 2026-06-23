<?php

$replacements = [
    "->with('success', 'Purchase invoice created.')" => "->with('success', __('messages.purchase.invoice_created'))",
    "->with('success', 'Purchase invoice posted and stock received.')" => "->with('success', __('messages.purchase.invoice_posted'))",
    "->with('success', 'Purchase invoice voided.')" => "->with('success', __('messages.purchase.invoice_voided'))",
    "->with('success', 'Posted purchase invoice updated.')" => "->with('success', __('messages.purchase.invoice_updated'))",
    "->with('success', 'User updated.')" => "->with('success', __('messages.user.updated'))",
    "->with('success', 'User permissions updated.')" => "->with('success', __('messages.user.permissions_updated'))",
    "->with('error', 'You cannot remove your own admin role.')" => "->with('error', __('messages.user.cannot_remove_own_admin'))",
    "->with('success', 'Fixed asset registered.')" => "->with('success', __('messages.fixed_asset.registered'))",
    "->with('success', 'Cash book entry recorded.')" => "->with('success', __('messages.cashbook.entry_recorded'))",
    "->with('success', 'Pick confirmed.')" => "->with('success', __('messages.pick.confirmed'))",
    "->with('success', 'POS session opened.')" => "->with('success', __('messages.pos.session_opened'))",
    "->with('success', 'POS session closed.')" => "->with('success', __('messages.pos.session_closed'))",
    "->with('success', 'Proforma invoice created.')" => "->with('success', __('messages.proforma.created'))",
    "->with('success', 'Currency created.')" => "->with('success', __('messages.currency.created'))",
    "->with('success', 'Currency updated.')" => "->with('success', __('messages.currency.updated'))",
    "->with('success', 'Currency deleted.')" => "->with('success', __('messages.currency.deleted'))",
    "->with('error', 'Cannot delete the base currency.')" => "->with('error', __('messages.currency.cannot_delete_base'))",
    "->with('success', 'Exchange rate updated.')" => "->with('success', __('messages.currency.rate_updated'))",
    "->with('success', 'Vehicle order created.')" => "->with('success', __('messages.vehicle.order_created'))",
    "->with('success', 'Order updated.')" => "->with('success', __('messages.vehicle.order_updated'))",
    "->with('success', 'Order deleted.')" => "->with('success', __('messages.vehicle.order_deleted'))",
    "->with('success', 'Expense recorded.')" => "->with('success', __('messages.vehicle.expense_recorded'))",
    "->with('success', 'Expense removed.')" => "->with('success', __('messages.vehicle.expense_removed'))",
    "->with('success', 'Delivery note created.')" => "->with('success', __('messages.delivery.created'))",
    "->with('success', 'Cheque recorded.')" => "->with('success', __('messages.cheque.recorded'))",
    "->with('success', 'Cheque status updated.')" => "->with('success', __('messages.cheque.status_updated'))",
    "->with('success', 'Manual journal posted.')" => "->with('success', __('messages.journal.posted'))",
    "->with('success', 'Kit component added.')" => "->with('success', __('messages.kit.component_added'))",
    "->with('success', 'Kit component removed.')" => "->with('success', __('messages.kit.component_removed'))",
    "->with('success', 'Alternative part added.')" => "->with('success', __('messages.kit.alternative_added'))",
    "->with('success', 'Alternative removed.')" => "->with('success', __('messages.kit.alternative_removed'))",
    "->with('success', 'Stock count session saved.')" => "->with('success', __('messages.stock_count.saved'))",
    "->with('success', 'Count posted — variances adjusted.')" => "->with('success', __('messages.stock_count.posted'))",
    "->with('success', 'Period closed.')" => "->with('success', __('messages.fiscal.closed'))",
    "->with('success', 'Period reopened.')" => "->with('success', __('messages.fiscal.reopened'))",
    "->with('success', 'Payment recorded.')" => "->with('success', __('messages.payment.recorded'))",
    "->with('success', 'Purchase return created.')" => "->with('success', __('messages.return.purchase_created'))",
    "->with('success', 'Return posted and stock removed.')" => "->with('success', __('messages.return.purchase_posted'))",
    "->with('success', 'Attendance saved.')" => "->with('success', __('messages.attendance.saved'))",
    "->with('success', 'Payroll generated.')" => "->with('success', __('messages.payroll.generated'))",
    "->with('success', 'Payroll posted.')" => "->with('success', __('messages.payroll.posted'))",
    "->with('success', 'Job card created.')" => "->with('success', __('messages.job_card.created'))",
    "->with('success', 'Job card status updated.')" => "->with('success', __('messages.job_card.status_updated'))",
    "->with('success', 'Settings saved.')" => "->with('success', __('messages.settings.saved'))",
    "->with('success', 'Discount rule added.')" => "->with('success', __('messages.settings.discount_added'))",
    "->with('success', 'Discount rule removed.')" => "->with('success', __('messages.settings.discount_removed'))",
    "->with('success', 'Quotation created.')" => "->with('success', __('messages.quotation.created'))",
    "->with('success', 'Record created successfully.')" => "->with('success', __('messages.master.created'))",
    "->with('success', 'Record updated successfully.')" => "->with('success', __('messages.master.updated'))",
    "->with('success', 'Record deleted successfully.')" => "->with('success', __('messages.master.deleted'))",
    "->with('success', 'Stock adjusted successfully.')" => "->with('success', __('messages.stock.adjusted'))",
    "->with('success', 'Part created successfully.')" => "->with('success', __('messages.part.created'))",
    "->with('success', 'Part updated successfully.')" => "->with('success', __('messages.part.updated'))",
    "->with('success', 'Part deleted successfully.')" => "->with('success', __('messages.part.deleted'))",
    "->with('success', 'Purchase order created.')" => "->with('success', __('messages.purchase_order.created'))",
    "->with('success', 'Sale return created.')" => "->with('success', __('messages.return.sale_created'))",
    "->with('success', 'Return posted and stock restored.')" => "->with('success', __('messages.return.sale_posted'))",
    "->with('success', 'Stock transfer created.')" => "->with('success', __('messages.stock.transfer_created'))",
    "->with('success', 'Transfer completed. Stock moved between branches.')" => "->with('success', __('messages.stock.transfer_completed'))",
];

foreach (glob(__DIR__ . '/../app/Http/Controllers/*.php') as $file) {
    $content = file_get_contents($file);
    $orig = $content;
    foreach ($replacements as $from => $to) {
        $content = str_replace($from, $to, $content);
    }
    if ($content !== $orig) {
        file_put_contents($file, $content);
        echo "updated: " . basename($file) . "\n";
    }
}

$special = [
    'PickTicketController.php' => [
        "->with('success', 'Pick ticket '.$ticket->pick_no.' created.')" =>
            "->with('success', __('messages.pick.created', ['no' => \$ticket->pick_no]))",
    ],
    'PosController.php' => [
        "->with('success', 'Sale completed: '.$invoice->invoice_no)" =>
            "->with('success', __('messages.pos.sale_completed', ['no' => \$invoice->invoice_no]))",
    ],
    'ProformaInvoiceController.php' => [
        "->with('success', 'Proforma converted to invoice '.$invoice->invoice_no)" =>
            "->with('success', __('messages.proforma.converted', ['no' => \$invoice->invoice_no]))",
    ],
    'QuotationController.php' => [
        "->with('success', 'Quotation converted to invoice '.$invoice->invoice_no)" =>
            "->with('success', __('messages.quotation.converted', ['no' => \$invoice->invoice_no]))",
    ],
    'JobCardController.php' => [
        "->with('success', 'Job card converted to invoice '.$invoice->invoice_no)" =>
            "->with('success', __('messages.job_card.converted', ['no' => \$invoice->invoice_no]))",
    ],
    'PurchaseOrderController.php' => [
        "->with('success', 'Purchase invoice '.$invoice->invoice_no.' created from PO.')" =>
            "->with('success', __('messages.purchase_order.converted', ['no' => \$invoice->invoice_no]))",
    ],
    'FixedAssetController.php' => [
        '->with(\'success\', "Depreciation posted for {$count} asset(s).")' =>
            "->with('success', __('messages.fixed_asset.depreciation_posted', ['count' => \$count]))",
    ],
];

foreach ($special as $name => $pairs) {
    $file = __DIR__ . '/../app/Http/Controllers/' . $name;
    $content = file_get_contents($file);
    foreach ($pairs as $from => $to) {
        $content = str_replace($from, $to, $content);
    }
    file_put_contents($file, $content);
}

echo "done\n";
