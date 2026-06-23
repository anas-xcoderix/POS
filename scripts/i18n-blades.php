<?php

/**
 * Batch-update blade views with __() translation helpers.
 * Run: php scripts/i18n-blades.php
 */

$root = dirname(__DIR__) . '/resources/views';

$titleMap = [
    "@php \$title = 'Users & Roles'; @endphp" => "@php \$title = __('nav.users_roles'); @endphp",
    "@php \$title = 'Audit Logs'; @endphp" => "@php \$title = __('nav.audit_logs'); @endphp",
    "@php \$title = 'Stock Batches'; @endphp" => "@php \$title = __('nav.stock_batches'); @endphp",
    "@php \$title = 'Register Fixed Asset'; @endphp" => "@php \$title = __('modules.register_fixed_asset'); @endphp",
    "@php \$title = 'Fixed Assets'; @endphp" => "@php \$title = __('nav.fixed_assets'); @endphp",
    "@php \$title = 'New Cash Book Entry'; @endphp" => "@php \$title = __('modules.new_cash_book_entry'); @endphp",
    "@php \$title = 'Cash Book'; @endphp" => "@php \$title = __('nav.cash_book'); @endphp",
    "@php \$title = 'Pick Tickets'; @endphp" => "@php \$title = __('nav.pick_tickets'); @endphp",
    "@php \$title = 'Purchase Invoices'; @endphp" => "@php \$title = __('modules.purchase_invoices'); @endphp",
    "@php \$title = 'Sales Invoices'; @endphp" => "@php \$title = __('modules.sales_invoices'); @endphp",
    "@php \$title = 'Point of Sale'; @endphp" => "@php \$title = __('nav.pos'); @endphp",
    "@php \$title = 'New Proforma'; @endphp" => "@php \$title = __('modules.new_proforma'); @endphp",
    "@php \$title = 'Proforma Invoices'; @endphp" => "@php \$title = __('nav.proforma'); @endphp",
    "@php \$title = 'Currencies'; @endphp" => "@php \$title = __('nav.currencies'); @endphp",
    "@php \$title = 'Dashboard'; @endphp" => "@php \$title = __('modules.dashboard'); @endphp",
];

$common = [
    'placeholder="Search invoice no..."' => 'placeholder="{{ __(\'pages.search.invoice\') }}"',
    'placeholder="Search invoice..."' => 'placeholder="{{ __(\'pages.search.purchase_invoice\') }}"',
    'placeholder="Search PO no..."' => 'placeholder="{{ __(\'pages.search.po\') }}"',
    'placeholder="Search quotation no..."' => 'placeholder="{{ __(\'pages.search.quotation\') }}"',
    'placeholder="Search receipt no..."' => 'placeholder="{{ __(\'pages.search.receipt\') }}"',
    'placeholder="Search return no..."' => 'placeholder="{{ __(\'pages.search.return\') }}"',
    'placeholder="Search transfer no..."' => 'placeholder="{{ __(\'pages.search.transfer\') }}"',
    'placeholder="Search count no..."' => 'placeholder="{{ __(\'pages.search.count\') }}"',
    'placeholder="Job no..."' => 'placeholder="{{ __(\'pages.search.job\') }}"',
    'placeholder="Search proforma no..."' => 'placeholder="{{ __(\'pages.search.proforma\') }}"',
    'placeholder="Search pick no..."' => 'placeholder="{{ __(\'pages.search.pick\') }}"',
    'placeholder="Search asset..."' => 'placeholder="{{ __(\'pages.search.asset\') }}"',
    'placeholder="Search..."' => 'placeholder="{{ __(\'ui.search_placeholder\') }}"',
    'placeholder="Reason for void..."' => 'placeholder="{{ __(\'pages.actions.reason_void\') }}"',
    'placeholder="Reason..."' => 'placeholder="{{ __(\'pages.actions.reason\') }}"',
    'placeholder="Service description"' => 'placeholder="{{ __(\'pages.job_cards.service_description\') }}"',
    'placeholder="Chassis or stock no..."' => 'placeholder="{{ __(\'forms.chassis_or_stock\') }}"',

    'title="No sales invoices"' => 'title="{{ __(\'pages.empty.sales_invoices\') }}"',
    'description="Create a sales invoice to bill customers and deduct stock."' => 'description="{{ __(\'pages.empty.sales_invoices_hint\') }}"',
    'title="No purchase invoices"' => 'title="{{ __(\'pages.empty.purchase_invoices\') }}"',
    'title="No purchase orders"' => 'title="{{ __(\'pages.empty.purchase_orders\') }}"',
    'description="Create a PO to order parts from vendors."' => 'description="{{ __(\'pages.empty.purchase_orders_hint\') }}"',
    'title="No quotations"' => 'title="{{ __(\'pages.empty.quotations\') }}"',
    'description="Create a quotation to send pricing to customers."' => 'description="{{ __(\'pages.empty.quotations_hint\') }}"',
    'title="No payment receipts"' => 'title="{{ __(\'pages.empty.payments\') }}"',
    'description="Record customer receipts or vendor payments."' => 'description="{{ __(\'pages.empty.payments_hint\') }}"',
    'title="No stock counts"' => 'title="{{ __(\'pages.empty.stock_counts\') }}"',
    'description="Start a physical count session to reconcile inventory."' => 'description="{{ __(\'pages.empty.stock_counts_hint\') }}"',
    'title="No count lines"' => 'title="{{ __(\'pages.empty.count_lines\') }}"',
    'description="This session has no items."' => 'description="{{ __(\'pages.empty.count_lines_hint\') }}"',
    'title="No job cards"' => 'title="{{ __(\'pages.empty.job_cards\') }}"',
    'description="Create a job card to track workshop work."' => 'description="{{ __(\'pages.empty.job_cards_hint\') }}"',
    'title="No WIP jobs"' => 'title="{{ __(\'pages.empty.wip\') }}"',
    'description="All job cards are completed or invoiced."' => 'description="{{ __(\'pages.empty.wip_hint\') }}"',
    'title="No delivery notes"' => 'title="{{ __(\'pages.empty.delivery_notes\') }}"',
    'description="Create delivery notes to record outbound shipments."' => 'description="{{ __(\'pages.empty.delivery_notes_hint\') }}"',
    'title="No items"' => 'title="{{ __(\'pages.empty.delivery_items\') }}"',
    'description="This delivery note has no line items."' => 'description="{{ __(\'pages.empty.delivery_items_hint\') }}"',
    'title="No sale returns"' => 'title="{{ __(\'pages.empty.sale_returns\') }}"',
    'description="Record customer returns to restore stock."' => 'description="{{ __(\'pages.empty.sale_returns_hint\') }}"',
    'title="No purchase returns"' => 'title="{{ __(\'pages.empty.purchase_returns\') }}"',
    'description="Record vendor returns to remove stock from inventory."' => 'description="{{ __(\'pages.empty.purchase_returns_hint\') }}"',
    'title="No stock transfers"' => 'title="{{ __(\'pages.empty.stock_transfers\') }}"',
    'description="Move stock between branches and locations."' => 'description="{{ __(\'pages.empty.stock_transfers_hint\') }}"',
    'title="No transactions"' => 'title="{{ __(\'pages.empty.transactions\') }}"',
    'description="No invoices or receipts found for this period."' => 'description="{{ __(\'pages.empty.customer_transactions_hint\') }}"',
    'description="No invoices or payments found for this period."' => 'description="{{ __(\'pages.empty.vendor_transactions_hint\') }}"',
    'title="No sales yet"' => 'title="{{ __(\'pages.empty.sales_yet\') }}"',
    'description="Your recent sales will appear here."' => 'description="{{ __(\'pages.empty.sales_yet_hint\') }}"',
    'title="No purchases yet"' => 'title="{{ __(\'pages.empty.purchases_yet\') }}"',
    'description="Your recent purchases will appear here."' => 'description="{{ __(\'pages.empty.purchases_yet_hint\') }}"',
    'title="No proforma invoices"' => 'title="{{ __(\'pages.empty.proforma\') }}"',
    'title="No pick tickets"' => 'title="{{ __(\'pages.empty.pick_tickets\') }}"',
    'description="Create a pick ticket from a posted sales invoice."' => 'description="{{ __(\'pages.empty.pick_tickets_hint\') }}"',
    'title="No cash book entries"' => 'title="{{ __(\'pages.empty.cash_book\') }}"',
    'title="No currencies"' => 'title="{{ __(\'pages.empty.currencies\') }}"',
    'title="No fixed assets"' => 'title="{{ __(\'pages.empty.fixed_assets\') }}"',
    'title="No stock batches"' => 'title="{{ __(\'pages.empty.stock_batches\') }}"',
    'title="No POS terminals"' => 'title="{{ __(\'pages.empty.pos_terminals\') }}"',
    'description="Run Desktop Parity seeder to create terminals."' => 'description="{{ __(\'pages.empty.pos_terminals_hint\') }}"',
    'title="No audit log entries"' => 'title="{{ __(\'pages.empty.audit_logs\') }}"',
    'title="No kit components"' => 'title="{{ __(\'pages.empty.kit_components\') }}"',
    'description="Add parts that make up this kit."' => 'description="{{ __(\'pages.empty.kit_components_hint\') }}"',
    'title="No alternatives"' => 'title="{{ __(\'pages.empty.alternatives\') }}"',
    'description="Add substitute parts for this item."' => 'description="{{ __(\'pages.empty.alternatives_hint\') }}"',

    '<option value="">All branches</option>' => '<option value="">{{ __(\'pages.filter.all_branches\') }}</option>',
    '<option value="">All statuses</option>' => '<option value="">{{ __(\'pages.filter.all_statuses\') }}</option>',
    '<option value="">All users</option>' => '<option value="">{{ __(\'pages.filter.all_users\') }}</option>',
    '<option value="">All parts</option>' => '<option value="">{{ __(\'pages.filter.all_parts\') }}</option>',
    '<option value="">All locations</option>' => '<option value="">{{ __(\'pages.filter.all_locations\') }}</option>',
    '<option value="">Base currency</option>' => '<option value="">{{ __(\'pages.filter.base_currency\') }}</option>',
    '<option value="">All</option>' => '<option value="">{{ __(\'ui.all\') }}</option>',

    '>New Invoice<' => '>{{ __(\'pages.actions.new_invoice\') }}<',
    '>Create Invoice<' => '>{{ __(\'pages.actions.create_invoice\') }}<',
    '>New Entry<' => '>{{ __(\'pages.actions.new_entry\') }}<',
    '>Convert to Invoice<' => '>{{ __(\'pages.actions.convert_to_invoice\') }}<',
    '>Convert to Sales Invoice<' => '>{{ __(\'pages.actions.convert_to_sales_invoice\') }}<',
    '>Back to Quotations<' => '>{{ __(\'pages.actions.back_to_quotations\') }}<',
    '>Back to Job Cards<' => '>{{ __(\'pages.actions.back_to_job_cards\') }}<',
    '>Back to Delivery Notes<' => '>{{ __(\'pages.actions.back_to_delivery_notes\') }}<',
    '>Back to Counts<' => '>{{ __(\'pages.actions.back_to_counts\') }}<',
    '>Back to Parts<' => '>{{ __(\'pages.actions.back_to_parts\') }}<',
    '>Back to Vehicles<' => '>{{ __(\'pages.actions.back_to_vehicles\') }}<',
    '>Confirm Void<' => '>{{ __(\'pages.actions.confirm_void\') }}<',
    '>Pick<' => '>{{ __(\'pages.actions.pick\') }}<',
    '>Return<' => '>{{ __(\'pages.actions.return\') }}<',
    '>Register<' => '>{{ __(\'pages.actions.register\') }}<',
    '>Open Counter<' => '>{{ __(\'pages.actions.open_counter\') }}<',
    '>Open Session<' => '>{{ __(\'pages.actions.open_session\') }}<',
    '>Save Permissions<' => '>{{ __(\'pages.actions.save_permissions\') }}<',
    '>Rights<' => '>{{ __(\'pages.actions.rights\') }}<',

    'label="From"' => 'label="{{ __(\'ui.from\') }}"',
    'label="To"' => 'label="{{ __(\'ui.to\') }}"',
    'label="Branch"' => 'label="{{ __(\'ui.branch\') }}"',
    'label="Date"' => 'label="{{ __(\'ui.date\') }}"',
    'label="Type"' => 'label="{{ __(\'ui.type\') }}"',
    'label="Amount"' => 'label="{{ __(\'ui.amount\') }}"',
    'label="Currency"' => 'label="{{ __(\'forms.currency\') }}"',
    'label="Remarks"' => 'label="{{ __(\'ui.remarks\') }}"',
    'label="Opening Float"' => 'label="{{ __(\'forms.opening_float\') }}"',
    'label="Closing Float"' => 'label="{{ __(\'forms.closing_float\') }}"',
    'label="Role"' => 'label="{{ __(\'pages.table.role\') }}"',
    'label="Active"' => 'label="{{ __(\'ui.active\') }}"',
    'label="Asset Code"' => 'label="{{ __(\'forms.asset_code\') }}"',
    'label="Name"' => 'label="{{ __(\'ui.name\') }}"',
    'label="Arabic Name"' => 'label="{{ __(\'ui.name_ar\') }}"',
    'label="Category"' => 'label="{{ __(\'forms.category\') }}"',
    'label="Location"' => 'label="{{ __(\'ui.location\') }}"',
    'label="Purchase Date"' => 'label="{{ __(\'forms.purchase_date\') }}"',
    'label="Purchase Value"' => 'label="{{ __(\'forms.purchase_value\') }}"',
    'label="Salvage Value"' => 'label="{{ __(\'forms.salvage_value\') }}"',
    'label="Useful Life (months)"' => 'label="{{ __(\'forms.useful_life\') }}"',
    'label="Max Discount %"' => 'label="{{ __(\'pages.users.max_discount_pct\') }}"',
    'label="Access All Branches"' => 'label="{{ __(\'pages.users.access_all_branches\') }}"',

    '>Cancel<' => '>{{ __(\'ui.cancel\') }}<',
    '>Save<' => '>{{ __(\'ui.save\') }}<',
    '>Filter<' => '>{{ __(\'ui.filter\') }}<',
    '>Post<' => '>{{ __(\'ui.post\') }}<',
    '>Void<' => '>{{ __(\'ui.void\') }}<',
    '>Edit<' => '>{{ __(\'ui.edit\') }}<',
    '>PDF<' => '>{{ __(\'ui.pdf\') }}<',
    '>Actions<' => '>{{ __(\'ui.actions\') }}<',
    '>Status<' => '>{{ __(\'ui.status\') }}<',
    '>Total<' => '>{{ __(\'ui.total\') }}<',
    '>Date<' => '>{{ __(\'ui.date\') }}<',
    '>Customer<' => '>{{ __(\'ui.customer\') }}<',
    '>Vendor<' => '>{{ __(\'ui.vendor\') }}<',
    '>Invoice<' => '>{{ __(\'pages.table.invoice\') }}<',
    '>Action<' => '>{{ __(\'pages.table.action\') }}<',
    '>User<' => '>{{ __(\'pages.table.user\') }}<',
    '>Account<' => '>{{ __(\'pages.table.account\') }}<',
    '>Description<' => '>{{ __(\'ui.description\') }}<',
    '>Balance<' => '>{{ __(\'ui.balance\') }}<',
    '>Yes<' => '>{{ __(\'ui.yes\') }}<',
    '>No<' => '>{{ __(\'ui.no\') }}<',
];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;
    $path = $file->getPathname();
    if (!str_ends_with($path, '.blade.php')) continue;
    $content = file_get_contents($path);
    $orig = $content;
    foreach (array_merge($titleMap, $common) as $from => $to) {
        $content = str_replace($from, $to, $content);
    }
    if ($content !== $orig) {
        file_put_contents($path, $content);
        echo str_replace($root . '/', '', $path) . "\n";
    }
}

echo "done\n";
