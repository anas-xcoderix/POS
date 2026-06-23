<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\SaleReturn;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\DB;

class CreditService
{
    public function __construct(private SettingService $settings) {}

    public function availableCredit(Customer $customer): ?float
    {
        $limit = (float) $customer->credit_limit;
        if ($limit <= 0) {
            return null;
        }

        return max(0, $limit - (float) $customer->balance);
    }

    public function assertCanCharge(Customer $customer, float $amount, string $invoiceType = 'credit'): void
    {
        if ($invoiceType !== 'credit' || ! $this->settings->getBool('enforce_credit_limit', true)) {
            return;
        }

        $limit = (float) $customer->credit_limit;
        if ($limit <= 0) {
            return;
        }

        $newBalance = (float) $customer->balance + $amount;
        if ($newBalance > $limit) {
            $available = $this->availableCredit($customer);

            throw new \RuntimeException(sprintf(
                'Credit limit exceeded for %s. Limit: %s, Balance: %s, Available: %s, Requested: %s',
                $customer->name,
                number_format($limit, 2),
                number_format((float) $customer->balance, 2),
                number_format($available ?? 0, 2),
                number_format($amount, 2)
            ));
        }
    }

    public function chargeForInvoice(SalesInvoice $invoice): void
    {
        if ($invoice->invoice_type !== 'credit') {
            return;
        }

        $amount = (float) $invoice->total_amount - (float) $invoice->paid_amount;
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($invoice, $amount) {
            $customer = Customer::lockForUpdate()->findOrFail($invoice->customer_id);
            $this->assertCanCharge($customer, $amount, 'credit');
            $customer->increment('balance', $amount);
        });
    }

    public function creditForReturn(SaleReturn $return): void
    {
        if (! $return->sales_invoice_id) {
            return;
        }

        $invoice = SalesInvoice::find($return->sales_invoice_id);
        if (! $invoice || $invoice->invoice_type !== 'credit') {
            return;
        }

        DB::transaction(function () use ($return) {
            $customer = Customer::lockForUpdate()->findOrFail($return->customer_id);
            $customer->decrement('balance', min((float) $customer->balance, (float) $return->total_amount));
        });
    }

    public function reverseInvoiceCharge(SalesInvoice $invoice): void
    {
        if ($invoice->invoice_type !== 'credit') {
            return;
        }

        $amount = (float) $invoice->total_amount - (float) $invoice->paid_amount;
        if ($amount <= 0) {
            return;
        }

        Customer::where('id', $invoice->customer_id)->decrement('balance', $amount);
    }
}
