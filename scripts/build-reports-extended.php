<?php

$reports = [
    ['account-list-crystal', 'AccountList', 'finance', 'gl-detail', 'finance', ['from', 'to']],
    ['account-summary', 'AccSum', 'finance', 'trial-balance', 'finance', ['as_of']],
    ['account-summary-detail', 'AccSum2', 'finance', 'gl-detail', 'finance', ['from', 'to']],
    ['aging-summary', 'Agingsum', 'finance', 'customer-aging', 'finance', ['as_of']],
    ['cash-by-customer', 'CashCustomer', 'finance', 'cash-transactions', 'finance', ['from', 'to', 'branch_id']],
    ['cash-register-alt', 'CashRep2', 'finance', 'cash-book-register', 'finance', ['from', 'to', 'branch_id']],
    ['cheque-report', 'ChequeRpt', 'finance', 'payment-receipts', 'finance', ['from', 'to']],
    ['chart-accounts-iaapco', 'ChAcIaapco', 'masters', 'trial-balance', 'finance', ['as_of']],
    ['customer-list-ar', 'CustomerArb', 'masters', 'customer-list', 'reports', [], 'ar'],
    ['customer-report-alt2', 'CustRep2', 'sales', 'sales-by-customer', 'reports', ['from', 'to', 'branch_id']],
    ['customer-report-summary', 'CustRep3', 'sales', 'sales-by-customer', 'reports', ['from', 'to', 'branch_id']],
    ['customer-type-ar', 'CustTypeArb', 'masters', 'customer-list', 'reports', [], 'ar'],
    ['customer-type-list', 'CustTypeList', 'masters', 'customer-list', 'reports', []],
    ['qty-difference', 'DiffQtyRep', 'inventory', 'physical-inventory', 'reports', ['branch_id']],
    ['discount-ar', 'DiscountArb', 'sales', 'sale-summary', 'reports', ['from', 'to'], 'ar'],
    ['delivery-note-rpt', 'DNote', 'sales', 'daily-transactions', 'reports', ['from', 'to']],
    ['delivery-note-alt', 'DNote2', 'sales', 'daily-transactions', 'reports', ['from', 'to']],
    ['duplicate-locations', 'DoubLocs', 'inventory', 'stock-by-location', 'reports', ['branch_id']],
    ['duplicate-parts', 'DoubParts', 'inventory', 'parts-master', 'reports', ['search']],
    ['franchise-monthly', 'FrMon', 'sales', 'monthly-sales-branch', 'reports', ['from', 'to']],
    ['ict-report', 'ICTReport', 'finance', 'income-statement', 'finance', ['from', 'to']],
    ['income-statement-alt1', 'Income1', 'finance', 'income-statement', 'finance', ['from', 'to']],
    ['income-statement-alt3', 'Income3', 'finance', 'income-statement', 'finance', ['from', 'to']],
    ['income-statement-stat', 'IncomeStat', 'finance', 'income-statement', 'finance', ['from', 'to']],
    ['istimara-status-rpt', 'IstimaraStatus', 'workshop', 'expiring-documents', 'hr', ['days']],
    ['license-status-rpt', 'LicenseStatus', 'workshop', 'expiring-documents', 'hr', ['days']],
    ['location-list', 'LocationList', 'masters', 'stock-by-location', 'reports', ['branch_id']],
    ['log-file-report', 'LogFileRep', 'masters', 'audit-log-report', 'audit-logs', ['from', 'to']],
    ['manufacturer-codes-ar', 'MfrCodeArb', 'masters', 'manufacturer-codes', 'reports', [], 'ar'],
    ['monthly-transactions', 'MonthTrans', 'sales', 'monthly-sales-branch', 'reports', ['from', 'to']],
    ['movement-report', 'MoveRep', 'inventory', 'stock-movements', 'reports', ['from', 'to', 'branch_id']],
    ['new-stock-report', 'NewStkRep', 'inventory', 'stock-listing', 'reports', ['branch_id']],
    ['other-services-rpt', 'OtherServices', 'workshop', 'job-cards', 'workshop', ['from', 'to']],
    ['physical-invoice', 'PhInv', 'inventory', 'physical-inventory', 'reports', ['branch_id']],
    ['physical-inventory-edit', 'PhyInvEdit', 'inventory', 'physical-inventory', 'reports', ['branch_id']],
    ['purchase-order-rpt', 'PoRep', 'purchase', 'pending-po', 'reports', ['branch_id']],
    ['purchase-report', 'PurcasheRpt', 'purchase', 'purchase-summary', 'reports', ['from', 'to', 'branch_id']],
    ['purchases-by-month', 'PurchasebyMonths', 'purchase', 'purchase-summary', 'reports', ['from', 'to']],
    ['pick-ticket-rpt', 'PickTicket', 'inventory', 'pick-ticket-list', 'reports', ['from', 'to', 'branch_id']],
    ['pick-ticket-alt', 'PickTicket2', 'inventory', 'pick-ticket-list', 'reports', ['from', 'to', 'branch_id']],
    ['sales-performa-rpt', 'SalesPerforma', 'sales', 'proforma-list', 'reports', ['from', 'to', 'branch_id']],
    ['showroom-quotation', 'SQoutation', 'sales', 'quotations', 'reports', ['from', 'to', 'branch_id']],
    ['showroom-report', 'ShowRoom', 'inventory', 'showroom-stock', 'reports', ['branch_id']],
    ['showroom-summary-sr1', 'SR1', 'sales', 'branch-performance', 'reports', ['from', 'to']],
    ['showroom-purchase-rpt', 'SRPurchaseReport', 'purchase', 'purchase-summary', 'reports', ['from', 'to']],
    ['showroom-orders-rpt', 'SRreportOrder', 'workshop', 'job-cards', 'workshop', ['from', 'to']],
    ['showroom-returns-rpt', 'SRtnSales1', 'sales', 'sales-returns', 'reports', ['from', 'to', 'branch_id']],
    ['showroom-sales-rpt', 'SSales', 'sales', 'sale-summary', 'reports', ['from', 'to', 'branch_id']],
    ['statement-account-ft', 'StateAccFT', 'finance', 'customer-statement-report', 'finance', ['from', 'to']],
    ['statement-account', 'StateAcct', 'finance', 'customer-statement-report', 'finance', ['from', 'to']],
    ['statement-account-alt', 'StateAcct3', 'finance', 'vendor-statement-report', 'finance', ['from', 'to']],
    ['stock-adjustment-rpt', 'StockAdj', 'inventory', 'stock-movements', 'reports', ['from', 'to', 'movement_type']],
    ['stock-order-rpt', 'StockOrd', 'inventory', 'stock-listing', 'reports', ['branch_id']],
    ['stock-report-legacy', 'stockreport', 'inventory', 'stock-listing', 'reports', ['branch_id']],
    ['stock-summary-legacy', 'stocksummary', 'inventory', 'stock-valuation', 'reports', ['branch_id']],
    ['attendance-time', 'TimeAtt', 'hr', 'payroll-summary', 'hr', ['from', 'to']],
    ['total-summary', 'TotalRep', 'sales', 'sale-summary', 'reports', ['from', 'to', 'branch_id']],
    ['transport-cash', 'TPCash', 'finance', 'cash-transactions', 'finance', ['from', 'to']],
    ['transport-report', 'TR', 'workshop', 'job-cards', 'workshop', ['from', 'to']],
    ['trial-balance-legacy', 'trailbalance', 'finance', 'trial-balance', 'finance', ['as_of']],
    ['transfer-receive', 'TransferRecieve', 'inventory', 'stock-transfers-report', 'reports', ['from', 'to']],
    ['transport-driver', 'TrDriver', 'workshop', 'job-cards', 'workshop', ['from', 'to']],
    ['transport-driver-alt', 'TrDriver2', 'workshop', 'job-cards', 'workshop', ['from', 'to']],
    ['units-list-ar', 'UnitArb', 'masters', 'parts-master', 'reports', [], 'ar'],
    ['units-list', 'UnitList', 'masters', 'parts-master', 'reports', []],
    ['variance-report', 'VarRep', 'inventory', 'physical-inventory', 'reports', ['branch_id']],
    ['vehicle-expenses-rpt', 'VehExpense', 'workshop', 'job-cards', 'workshop', ['from', 'to']],
    ['vehicle-orders-report', 'VehicleOrder', 'workshop', 'job-cards', 'workshop', ['from', 'to']],
    ['wip-report-full', 'WIPReport', 'workshop', 'workshop-wip', 'workshop', ['branch_id']],
    ['daily-cash-cr', 'cr-Cash', 'finance', 'cash-book-register', 'finance', ['from', 'to', 'branch_id']],
    ['daily-trans-alt1', 'DailyTrans1', 'sales', 'daily-transactions', 'reports', ['from', 'to', 'branch_id']],
    ['daily-trans-alt3', 'DailyTrans3', 'sales', 'daily-transactions', 'reports', ['from', 'to', 'branch_id']],
    ['general-report-legacy', 'GenRptOld', 'finance', 'journal-entries-report', 'finance', ['from', 'to']],
    ['no-count-parts', 'nocount', 'inventory', 'parts-without-movement', 'reports', ['days']],
    ['shipping-status', 'ShipSit', 'sales', 'daily-transactions', 'reports', ['from', 'to']],
    ['return-sales-alt', 'RtnSales1', 'sales', 'sales-returns', 'reports', ['from', 'to', 'branch_id']],
    ['return-sales-total', 'RtSaleT', 'sales', 'sales-returns', 'reports', ['from', 'to', 'branch_id']],
    ['workshop-stock-legacy', 'workshopstock', 'inventory', 'workshop-stock', 'reports', ['branch_id']],
    ['showroom-vehicle-stock', 'SRstock', 'inventory', null, 'showroom-vehicles', ['branch_id'], null, true],
    ['showroom-vehicle-sales-rpt', 'ShowRoomSales', 'sales', null, 'showroom-vehicles', ['from', 'to', 'branch_id'], null, true],
    ['chassis-tracker', 'ChassisTrack', 'inventory', null, 'showroom-vehicles', ['search', 'branch_id'], null, true],
    ['showroom-transfer-rpt', 'SRTransfer', 'inventory', null, 'showroom-vehicles', ['from', 'to', 'branch_id'], null, true],
];

$titles = include __DIR__.'/../lang/en/reports_extra.php';

$out = "<?php\n\nreturn [\n    'reports' => [\n";
foreach ($reports as $r) {
    [$key, $legacy, $cat, $handler, $perm, $filters] = $r;
    $locale = $r[6] ?? null;
    $native = $r[7] ?? false;
    $title = $titles[$key] ?? ucwords(str_replace('-', ' ', $key));
    $filtersStr = empty($filters) ? '[]' : "['".implode("', '", $filters)."']";
    $out .= "        '{$key}' => [\n";
    $out .= "            'legacy' => '{$legacy}',\n";
    $out .= "            'category' => '{$cat}',\n";
    $out .= "            'title' => ".var_export($title, true).",\n";
    $out .= "            'title_ar' => ".var_export($title, true).",\n";
    $out .= "            'permission' => '{$perm}',\n";
    $out .= "            'filters' => {$filtersStr},\n";
    if ($handler) {
        $out .= "            'handler' => '{$handler}',\n";
    }
    if ($locale) {
        $out .= "            'locale' => '{$locale}',\n";
    }
    if ($native) {
        $out .= "            'native' => true,\n";
    }
    $out .= "        ],\n";
}
$out .= "    ],\n];\n";
file_put_contents(__DIR__.'/../config/reports_extended.php', $out);
echo count($reports)." reports written\n";
