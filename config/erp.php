<?php

return [

    'roles' => [
        'admin' => ['*'],
        'manager' => [
            'dashboard', 'parts', 'stock', 'quotations', 'sales', 'sale-returns', 'purchase-returns',
            'stock-transfers', 'purchase-orders', 'purchase-invoices', 'payments', 'stock-counts',
            'customers', 'vendors', 'branches', 'locations', 'brands', 'origins', 'franchises', 'units',
            'settings', 'users', 'parts.import', 'finance', 'cheques', 'delivery-notes', 'vehicle-orders',
            'workshop', 'hr', 'reports',
            'proforma', 'pos', 'pick-tickets', 'cash-book', 'fixed-assets', 'currencies', 'audit-logs', 'stock-batches',
            'showroom-vehicles', 'transport',
        ],
        'sales' => [
            'dashboard', 'parts', 'stock', 'quotations', 'sales', 'sale-returns', 'delivery-notes',
            'customers', 'payments', 'reports',
            'proforma', 'pos', 'pick-tickets', 'showroom-vehicles', 'transport',
        ],
        'warehouse' => [
            'dashboard', 'parts', 'stock', 'stock-transfers', 'stock-counts', 'purchase-orders',
            'purchase-invoices', 'purchase-returns', 'vendors', 'parts.import', 'reports',
            'pick-tickets', 'stock-batches',
        ],
        'user' => ['dashboard'],
    ],

    'granular_permissions' => [
        'sales.edit_posted' => 'Edit posted sales invoices',
        'sales.void' => 'Void sales invoices',
        'purchase.edit_posted' => 'Edit posted purchase invoices',
        'purchase.void' => 'Void purchase invoices',
        'pos.delete_line' => 'Delete POS sale lines',
        'finance.unpost_journal' => 'Unpost journal entries',
        'stock.transfer_qty' => 'Override stock transfer quantities',
        'reports.all_branches' => 'View reports across all branches',
        'quotation.only' => 'Quotations only (no sales invoices)',
    ],

    'default_settings' => [
        'default_vat_rate' => '15',
        'price_level_retail' => '1',
        'price_level_wholesale' => '2',
        'price_level_corporate' => '3',
        'enforce_credit_limit' => '1',
        'company_name' => 'PartFlow',
        'auto_post_gl' => '1',
        'gl_cash' => '1000',
        'gl_accounts_receivable' => '1100',
        'gl_inventory' => '1200',
        'gl_vat_input' => '1300',
        'gl_accounts_payable' => '2100',
        'gl_vat_payable' => '2200',
        'gl_sales_revenue' => '4000',
        'gl_cogs' => '5000',
        'gl_retained_earnings' => '3100',
        'gl_salary_expense' => '5110',
        'gl_salaries_payable' => '2300',
        'gosi_employee_rate' => '9.75',
        'gosi_employer_rate' => '11.75',
    ],

    'gl_accounts' => [
        'cash' => '1000',
        'accounts_receivable' => '1100',
        'inventory' => '1200',
        'vat_input' => '1300',
        'accounts_payable' => '2100',
        'vat_payable' => '2200',
        'sales_revenue' => '4000',
        'cogs' => '5000',
        'retained_earnings' => '3100',
        'salary_expense' => '5110',
        'salaries_payable' => '2300',
    ],

];
