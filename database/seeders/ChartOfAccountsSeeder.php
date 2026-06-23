<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['1000', 'Cash on Hand', 'asset'],
            ['1100', 'Accounts Receivable', 'asset'],
            ['1200', 'Inventory', 'asset'],
            ['1300', 'VAT Input (Recoverable)', 'asset'],
            ['2100', 'Accounts Payable', 'liability'],
            ['2200', 'VAT Payable (Output)', 'liability'],
            ['3100', 'Retained Earnings', 'equity'],
            ['4000', 'Sales Revenue', 'revenue'],
            ['5000', 'Cost of Goods Sold', 'expense'],
            ['5100', 'Operating Expenses', 'expense'],
        ];

        foreach ($accounts as [$code, $name, $type]) {
            Account::updateOrCreate(
                ['account_code' => $code],
                ['name' => $name, 'account_type' => $type, 'is_active' => true]
            );
        }
    }
}
