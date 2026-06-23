<?php

return [
    'saved' => 'Saved successfully.',
    'deleted' => 'Deleted successfully.',
    'updated' => 'Updated successfully.',
    'sales' => [
        'invoice_created' => 'Sales invoice created.',
        'invoice_posted' => 'Invoice posted and stock updated.',
        'invoice_voided' => 'Invoice voided.',
        'invoice_updated' => 'Posted invoice updated.',
    ],
    'purchase' => [
        'invoice_created' => 'Purchase invoice created.',
        'invoice_posted' => 'Purchase invoice posted and stock received.',
        'invoice_voided' => 'Purchase invoice voided.',
        'invoice_updated' => 'Posted purchase invoice updated.',
    ],
    'proforma' => [
        'created' => 'Proforma invoice created.',
        'converted' => 'Proforma converted to invoice :no.',
    ],
    'pos' => [
        'session_opened' => 'POS session opened.',
        'session_closed' => 'POS session closed.',
        'session_already_open' => 'This terminal already has an open session.',
        'session_not_open' => 'This session is not open.',
        'sale_completed' => 'Sale completed: :no.',
    ],
    'pick' => [
        'created' => 'Pick ticket :no created.',
        'confirmed' => 'Pick confirmed.',
    ],
    'cashbook' => [
        'entry_recorded' => 'Cash book entry recorded.',
    ],
    'fixed_asset' => [
        'registered' => 'Fixed asset registered.',
        'depreciation_posted' => 'Depreciation posted for :count asset(s).',
    ],
    'currency' => [
        'created' => 'Currency created.',
        'updated' => 'Currency updated.',
        'deleted' => 'Currency deleted.',
        'rate_updated' => 'Exchange rate updated.',
        'cannot_delete_base' => 'Cannot delete the base currency.',
    ],
    'user' => [
        'updated' => 'User updated.',
        'permissions_updated' => 'User permissions updated.',
        'cannot_remove_own_admin' => 'You cannot remove your own admin role.',
    ],
    'showroom' => [
        'vehicle_registered' => 'Showroom vehicle registered.',
        'transfer_created' => 'Vehicle transfer initiated.',
        'transfer_received' => 'Vehicle transfer received.',
        'vehicle_sold' => 'Vehicle marked as sold.',
        'no_vehicles' => 'No showroom vehicles found.',
    ],
    'delivery' => [
        'created' => 'Delivery note created.',
    ],
    'cheque' => [
        'recorded' => 'Cheque recorded.',
        'status_updated' => 'Cheque status updated.',
    ],
    'journal' => [
        'posted' => 'Manual journal posted.',
    ],
    'kit' => [
        'component_added' => 'Kit component added.',
        'component_removed' => 'Kit component removed.',
        'alternative_added' => 'Alternative part added.',
        'alternative_removed' => 'Alternative removed.',
    ],
    'stock_count' => [
        'saved' => 'Stock count session saved.',
        'posted' => 'Count posted — variances adjusted.',
    ],
    'fiscal' => [
        'closed' => 'Period closed.',
        'reopened' => 'Period reopened.',
    ],
    'payment' => [
        'recorded' => 'Payment recorded.',
    ],
    'return' => [
        'purchase_created' => 'Purchase return created.',
        'purchase_posted' => 'Return posted and stock removed.',
        'sale_created' => 'Sale return created.',
        'sale_posted' => 'Return posted and stock restored.',
    ],
    'attendance' => [
        'saved' => 'Attendance saved.',
    ],
    'payroll' => [
        'generated' => 'Payroll generated.',
        'posted' => 'Payroll posted.',
    ],
    'job_card' => [
        'created' => 'Job card created.',
        'status_updated' => 'Job card status updated.',
        'converted' => 'Job card converted to invoice :no.',
    ],
    'settings' => [
        'saved' => 'Settings saved.',
        'discount_added' => 'Discount rule added.',
        'discount_removed' => 'Discount rule removed.',
    ],
    'quotation' => [
        'created' => 'Quotation created.',
        'converted' => 'Quotation converted to invoice :no.',
    ],
    'master' => [
        'created' => 'Record created successfully.',
        'updated' => 'Record updated successfully.',
        'deleted' => 'Record deleted successfully.',
    ],
    'stock' => [
        'adjusted' => 'Stock adjusted successfully.',
        'transfer_created' => 'Stock transfer created.',
        'transfer_completed' => 'Transfer completed. Stock moved between branches.',
    ],
    'part' => [
        'created' => 'Part created successfully.',
        'updated' => 'Part updated successfully.',
        'deleted' => 'Part deleted successfully.',
    ],
    'purchase_order' => [
        'created' => 'Purchase order created.',
        'converted' => 'Purchase invoice :no created from PO.',
    ],
    'vehicle' => [
        'order_created' => 'Vehicle order created.',
        'order_updated' => 'Order updated.',
        'order_deleted' => 'Order deleted.',
        'expense_recorded' => 'Expense recorded.',
        'expense_removed' => 'Expense removed.',
    ],
    'transport' => [
        'shipment_created' => 'Transport shipment created.',
        'status_updated' => 'Shipment status updated.',
        'voucher_created' => 'Transport cash voucher created.',
        'voucher_posted' => 'Transport voucher posted to cash book.',
    ],
    'hr' => [
        'payroll_generated' => 'Payroll generated.',
        'payroll_posted' => 'Payroll posted to general ledger.',
        'payroll_already_posted' => 'Payroll for this period is already posted.',
        'payroll_not_draft' => 'Only draft payroll can be edited or regenerated.',
        'payroll_not_posted' => 'Payroll must be posted before payment.',
        'payroll_already_paid' => 'This payroll has already been paid.',
        'payroll_paid' => 'Payroll marked as paid.',
        'payroll_line_updated' => 'Payroll line updated.',
        'payroll_regenerated' => 'Payroll regenerated.',
        'leave_created' => 'Leave request submitted.',
        'leave_approved' => 'Leave request approved.',
        'leave_rejected' => 'Leave request rejected.',
        'leave_not_pending' => 'Leave request is not pending approval.',
        'attendance_saved' => 'Attendance saved.',
    ],
];
