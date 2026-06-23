<?php

return [

    'roles' => [
        'admin' => ['*'],
        'manager' => [
            'dashboard', 'parts', 'stock', 'quotations', 'sales', 'sale-returns',
            'stock-transfers', 'purchase-orders', 'purchase-invoices',
            'customers', 'vendors', 'branches', 'locations', 'brands', 'origins', 'franchises',
            'settings', 'users', 'parts.import', 'finance',
            'workshop', 'hr', 'reports',
        ],
        'sales' => [
            'dashboard', 'parts', 'stock', 'quotations', 'sales', 'sale-returns', 'customers', 'reports',
        ],
        'warehouse' => [
            'dashboard', 'parts', 'stock', 'stock-transfers', 'purchase-orders',
            'purchase-invoices', 'vendors', 'parts.import', 'reports',
        ],
        'user' => ['dashboard'],
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
    ],

];
