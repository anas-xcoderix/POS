<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PaymentReceipt;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Models\SaleReturn;
use App\Models\SalesInvoice;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    public function __construct(
        private SettingService $settings,
        private FiscalPeriodService $fiscalPeriod,
    ) {}

    public function isAutoPostEnabled(): bool
    {
        return $this->settings->getBool('auto_post_gl', true);
    }

    public function accountByCode(string $code): Account
    {
        $account = Account::where('account_code', $code)->where('is_active', true)->first();
        if (! $account) {
            throw new \RuntimeException("GL account [{$code}] not found. Run chart of accounts seeder or create the account.");
        }

        return $account;
    }

    public function glCode(string $key): string
    {
        return $this->settings->get("gl_{$key}") ?? config("erp.gl_accounts.{$key}", '');
    }

    public function postEntry(array $header, array $lines, ?string $referenceType = null, ?int $referenceId = null): JournalEntry
    {
        return DB::transaction(function () use ($header, $lines, $referenceType, $referenceId) {
            if (! empty($header['entry_date'])) {
                $this->fiscalPeriod->assertOpen($header['entry_date']);
            }

            if ($referenceType && $referenceId) {
                $exists = JournalEntry::where('reference_type', $referenceType)
                    ->where('reference_id', $referenceId)
                    ->where('status', 'posted')
                    ->where('entry_type', '!=', 'reversal')
                    ->exists();
                if ($exists) {
                    throw new \RuntimeException('Journal entry already posted for this document.');
                }
            }

            $totalDebit = round(collect($lines)->sum('debit'), 2);
            $totalCredit = round(collect($lines)->sum('credit'), 2);
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \RuntimeException("Journal entry not balanced: DR {$totalDebit} vs CR {$totalCredit}");
            }

            $entry = JournalEntry::create(array_merge([
                'entry_no' => $this->nextEntryNo(),
                'branch_id' => Branch::query()->value('id'),
                'entry_type' => 'auto',
            ], $header, [
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'status' => 'posted',
            ]));

            foreach ($lines as $line) {
                if ((float) $line['debit'] === 0.0 && (float) $line['credit'] === 0.0) {
                    continue;
                }

                $account = Account::lockForUpdate()->findOrFail($line['account_id']);
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $account->id,
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'description' => $line['description'] ?? null,
                ]);

                $this->applyBalance($account, (float) $line['debit'], (float) $line['credit']);
            }

            return $entry->fresh(['lines.account', 'branch']);
        });
    }

    public function postSalesInvoice(SalesInvoice $invoice, ?int $userId = null): ?JournalEntry
    {
        if (! $this->isAutoPostEnabled()) {
            return null;
        }

        $invoice->load(['items.part', 'customer', 'branch']);
        $netSales = round((float) $invoice->total_amount - (float) $invoice->vat_amount, 2);
        $vat = round((float) $invoice->vat_amount, 2);
        $total = round((float) $invoice->total_amount, 2);

        $receivableAccount = $invoice->invoice_type === 'credit'
            ? $this->accountByCode($this->glCode('accounts_receivable'))
            : $this->accountByCode($this->glCode('cash'));

        $lines = [
            ['account_id' => $receivableAccount->id, 'debit' => $total, 'credit' => 0, 'description' => 'Sale '.$invoice->invoice_no],
            ['account_id' => $this->accountByCode($this->glCode('sales_revenue'))->id, 'debit' => 0, 'credit' => $netSales, 'description' => 'Revenue'],
        ];

        if ($vat > 0) {
            $lines[] = ['account_id' => $this->accountByCode($this->glCode('vat_payable'))->id, 'debit' => 0, 'credit' => $vat, 'description' => 'Output VAT'];
        }

        $cogs = round($invoice->items->sum(function ($item) {
            $cost = (float) $item->unit_cost > 0 ? (float) $item->unit_cost : (float) ($item->part?->cost_price ?? 0);

            return (float) $item->quantity * $cost;
        }), 2);

        if ($cogs > 0) {
            $lines[] = ['account_id' => $this->accountByCode($this->glCode('cogs'))->id, 'debit' => $cogs, 'credit' => 0, 'description' => 'COGS'];
            $lines[] = ['account_id' => $this->accountByCode($this->glCode('inventory'))->id, 'debit' => 0, 'credit' => $cogs, 'description' => 'Inventory relief'];
        }

        return $this->postEntry([
            'entry_no' => $this->nextEntryNo(),
            'branch_id' => $invoice->branch_id,
            'entry_date' => $invoice->invoice_date,
            'description' => 'Sales invoice '.$invoice->invoice_no.' — '.$invoice->customer?->name,
            'created_by' => $userId,
        ], $lines, SalesInvoice::class, $invoice->id);
    }

    public function postPurchaseInvoice(PurchaseInvoice $invoice, ?int $userId = null): ?JournalEntry
    {
        if (! $this->isAutoPostEnabled()) {
            return null;
        }

        $invoice->load(['items.part', 'vendor', 'branch']);
        $subtotal = round((float) $invoice->subtotal, 2);
        $vat = round((float) $invoice->vat_amount, 2);
        $total = round((float) $invoice->total_amount, 2);

        $lines = [
            ['account_id' => $this->accountByCode($this->glCode('inventory'))->id, 'debit' => $subtotal, 'credit' => 0, 'description' => 'Stock received'],
            ['account_id' => $this->accountByCode($this->glCode('accounts_payable'))->id, 'debit' => 0, 'credit' => $total, 'description' => 'Vendor payable'],
        ];

        if ($vat > 0) {
            $lines[] = ['account_id' => $this->accountByCode($this->glCode('vat_input'))->id, 'debit' => $vat, 'credit' => 0, 'description' => 'Input VAT'];
        }

        return $this->postEntry([
            'entry_no' => $this->nextEntryNo(),
            'branch_id' => $invoice->branch_id,
            'entry_date' => $invoice->invoice_date,
            'description' => 'Purchase invoice '.$invoice->invoice_no.' — '.$invoice->vendor?->name,
            'created_by' => $userId,
        ], $lines, PurchaseInvoice::class, $invoice->id);
    }

    public function postSaleReturn(SaleReturn $return, ?int $userId = null): ?JournalEntry
    {
        if (! $this->isAutoPostEnabled()) {
            return null;
        }

        $return->load(['items.part', 'customer', 'salesInvoice']);
        $total = round((float) $return->total_amount, 2);
        if ($total <= 0) {
            return null;
        }

        $invoice = $return->salesInvoice;
        $isCredit = $invoice && $invoice->invoice_type === 'credit';
        $offsetAccount = $isCredit
            ? $this->accountByCode($this->glCode('accounts_receivable'))
            : $this->accountByCode($this->glCode('cash'));

        $lines = [
            ['account_id' => $this->accountByCode($this->glCode('sales_revenue'))->id, 'debit' => $total, 'credit' => 0, 'description' => 'Sales return'],
            ['account_id' => $offsetAccount->id, 'debit' => 0, 'credit' => $total, 'description' => 'Refund / credit note'],
        ];

        return $this->postEntry([
            'entry_no' => $this->nextEntryNo(),
            'branch_id' => $return->branch_id,
            'entry_date' => $return->return_date,
            'description' => 'Sale return '.$return->return_no,
            'created_by' => $userId,
        ], $lines, SaleReturn::class, $return->id);
    }

    public function postPurchaseReturn(PurchaseReturn $return, ?int $userId = null): ?JournalEntry
    {
        if (! $this->isAutoPostEnabled()) {
            return null;
        }

        $return->load(['items.part', 'vendor']);
        $total = round((float) $return->total_amount, 2);
        if ($total <= 0) {
            return null;
        }

        $subtotal = round((float) $return->subtotal, 2);
        $vat = round((float) $return->vat_amount, 2);

        $lines = [
            ['account_id' => $this->accountByCode($this->glCode('accounts_payable'))->id, 'debit' => $total, 'credit' => 0, 'description' => 'Purchase return'],
            ['account_id' => $this->accountByCode($this->glCode('inventory'))->id, 'debit' => 0, 'credit' => $subtotal, 'description' => 'Stock returned'],
        ];

        if ($vat > 0) {
            $lines[] = ['account_id' => $this->accountByCode($this->glCode('vat_input'))->id, 'debit' => 0, 'credit' => $vat, 'description' => 'Input VAT reversal'];
        }

        return $this->postEntry([
            'entry_no' => $this->nextEntryNo(),
            'branch_id' => $return->branch_id,
            'entry_date' => $return->return_date,
            'description' => 'Purchase return '.$return->return_no,
            'created_by' => $userId,
        ], $lines, PurchaseReturn::class, $return->id);
    }

    public function postPaymentReceipt(PaymentReceipt $receipt, ?int $userId = null): ?JournalEntry
    {
        if (! $this->isAutoPostEnabled()) {
            return null;
        }

        $amount = round((float) $receipt->amount, 2);
        if ($amount <= 0) {
            return null;
        }

        $cash = $this->accountByCode($this->glCode('cash'));

        if ($receipt->party_type === 'customer') {
            $lines = [
                ['account_id' => $cash->id, 'debit' => $amount, 'credit' => 0, 'description' => 'Customer receipt'],
                ['account_id' => $this->accountByCode($this->glCode('accounts_receivable'))->id, 'debit' => 0, 'credit' => $amount, 'description' => 'AR settlement'],
            ];
        } else {
            $lines = [
                ['account_id' => $this->accountByCode($this->glCode('accounts_payable'))->id, 'debit' => $amount, 'credit' => 0, 'description' => 'Vendor payment'],
                ['account_id' => $cash->id, 'debit' => 0, 'credit' => $amount, 'description' => 'Cash paid'],
            ];
        }

        return $this->postEntry([
            'entry_no' => $this->nextEntryNo(),
            'branch_id' => $receipt->branch_id,
            'entry_date' => $receipt->receipt_date,
            'description' => 'Payment '.$receipt->receipt_no,
            'created_by' => $userId,
        ], $lines, PaymentReceipt::class, $receipt->id);
    }

    public function postManualJournal(array $header, array $lines, ?int $userId = null): JournalEntry
    {
        return $this->postEntry(array_merge($header, [
            'entry_no' => $this->nextEntryNo(),
            'entry_type' => 'manual',
            'created_by' => $userId,
        ]), $lines);
    }

    public function reverseDocumentEntry(string $referenceType, int $referenceId, string $description, ?int $userId = null): ?JournalEntry
    {
        $original = JournalEntry::with('lines')
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('status', 'posted')
            ->where('entry_type', '!=', 'reversal')
            ->latest()
            ->first();

        if (! $original) {
            return null;
        }

        $lines = $original->lines->map(fn ($line) => [
            'account_id' => $line->account_id,
            'debit' => $line->credit,
            'credit' => $line->debit,
            'description' => 'Reversal: '.($line->description ?? ''),
        ])->all();

        return $this->postEntry([
            'entry_no' => $this->nextEntryNo(),
            'branch_id' => $original->branch_id,
            'entry_date' => now()->toDateString(),
            'description' => $description,
            'entry_type' => 'reversal',
            'created_by' => $userId,
        ], $lines);
    }

    public function trialBalance(?string $from = null, ?string $to = null): Collection
    {
        $accounts = Account::where('is_active', true)->orderBy('account_code')->get();

        return $accounts->map(function (Account $account) use ($from, $to) {
            $totals = $this->accountActivity($account->id, $from, $to);
            $net = $totals['debit'] - $totals['credit'];
            if ($account->isDebitNormal()) {
                $debit = $net >= 0 ? $net : 0;
                $credit = $net < 0 ? abs($net) : 0;
            } else {
                $credit = $net <= 0 ? abs($net) : 0;
                $debit = $net > 0 ? $net : 0;
            }

            return [
                'account' => $account,
                'debit' => round($debit, 2),
                'credit' => round($credit, 2),
            ];
        })->filter(fn ($row) => $row['debit'] > 0 || $row['credit'] > 0);
    }

    public function incomeStatement(string $from, string $to): array
    {
        $revenue = $this->sumByType('revenue', $from, $to);
        $expenses = $this->sumByType('expense', $from, $to);
        $netIncome = round($revenue - $expenses, 2);

        return compact('revenue', 'expenses', 'netIncome', 'from', 'to');
    }

    public function balanceSheet(?string $asOf = null): array
    {
        $asOf = $asOf ?? now()->toDateString();
        $assets = $this->balanceByType('asset', $asOf);
        $liabilities = $this->balanceByType('liability', $asOf);
        $equity = $this->balanceByType('equity', $asOf);
        $totalAssets = round($assets->sum('balance'), 2);
        $totalLiabilities = round($liabilities->sum('balance'), 2);
        $totalEquity = round($equity->sum('balance'), 2);

        return compact('assets', 'liabilities', 'equity', 'totalAssets', 'totalLiabilities', 'totalEquity', 'asOf');
    }

    public function customerAging(): Collection
    {
        return SalesInvoice::with('customer')
            ->where('status', 'posted')
            ->where('invoice_type', 'credit')
            ->get()
            ->map(function (SalesInvoice $inv) {
                $outstanding = max(0, (float) $inv->total_amount - (float) $inv->paid_amount);
                $days = Carbon::parse($inv->invoice_date)->diffInDays(now());

                return [
                    'invoice' => $inv,
                    'customer' => $inv->customer,
                    'outstanding' => $outstanding,
                    'days' => $days,
                    'bucket' => $this->agingBucket($days),
                ];
            })
            ->filter(fn ($row) => $row['outstanding'] > 0)
            ->groupBy(fn ($row) => $row['customer']?->id)
            ->map(function ($rows) {
                $customer = $rows->first()['customer'];
                $buckets = ['current' => 0, '31_60' => 0, '61_90' => 0, 'over_90' => 0];
                foreach ($rows as $row) {
                    $buckets[$row['bucket']] += $row['outstanding'];
                }

                return [
                    'customer' => $customer,
                    'buckets' => $buckets,
                    'total' => array_sum($buckets),
                    'invoices' => $rows,
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    public function vendorAging(): Collection
    {
        return PurchaseInvoice::with('vendor')
            ->where('status', 'posted')
            ->get()
            ->map(function (PurchaseInvoice $inv) {
                $outstanding = max(0, (float) $inv->total_amount - (float) $inv->paid_amount);
                $days = Carbon::parse($inv->invoice_date)->diffInDays(now());

                return [
                    'invoice' => $inv,
                    'vendor' => $inv->vendor,
                    'outstanding' => $outstanding,
                    'days' => $days,
                    'bucket' => $this->agingBucket($days),
                ];
            })
            ->filter(fn ($row) => $row['outstanding'] > 0)
            ->groupBy(fn ($row) => $row['vendor']?->id)
            ->map(function ($rows) {
                $vendor = $rows->first()['vendor'];
                $buckets = ['current' => 0, '31_60' => 0, '61_90' => 0, 'over_90' => 0];
                foreach ($rows as $row) {
                    $buckets[$row['bucket']] += $row['outstanding'];
                }

                return [
                    'vendor' => $vendor,
                    'buckets' => $buckets,
                    'total' => array_sum($buckets),
                    'invoices' => $rows,
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    protected function agingBucket(int $days): string
    {
        return match (true) {
            $days <= 30 => 'current',
            $days <= 60 => '31_60',
            $days <= 90 => '61_90',
            default => 'over_90',
        };
    }

    protected function nextEntryNo(): string
    {
        $seq = JournalEntry::count() + 1;

        return 'JE-'.now()->format('Ymd').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    protected function applyBalance(Account $account, float $debit, float $credit): void
    {
        $change = $account->isDebitNormal() ? ($debit - $credit) : ($credit - $debit);
        $account->increment('current_balance', $change);
    }

    protected function accountActivity(int $accountId, ?string $from, ?string $to): array
    {
        $query = JournalEntryLine::query()
            ->where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($from, $to) {
                $q->where('status', 'posted');
                if ($from) {
                    $q->whereDate('entry_date', '>=', $from);
                }
                if ($to) {
                    $q->whereDate('entry_date', '<=', $to);
                }
            });

        return [
            'debit' => (float) $query->sum('debit'),
            'credit' => (float) $query->sum('credit'),
        ];
    }

    protected function sumByType(string $type, string $from, string $to): float
    {
        $accounts = Account::where('account_type', $type)->where('is_active', true)->pluck('id');
        $debit = 0;
        $credit = 0;

        foreach ($accounts as $accountId) {
            $activity = $this->accountActivity($accountId, $from, $to);
            $debit += $activity['debit'];
            $credit += $activity['credit'];
        }

        return $type === 'revenue'
            ? round($credit - $debit, 2)
            : round($debit - $credit, 2);
    }

    protected function balanceByType(string $type, string $asOf): Collection
    {
        return Account::where('account_type', $type)->where('is_active', true)->orderBy('account_code')->get()
            ->map(function (Account $account) use ($asOf) {
                $opening = (float) $account->opening_balance;
                $activity = $this->accountActivity($account->id, null, $asOf);
                $net = $activity['debit'] - $activity['credit'];
                $balance = $account->isDebitNormal()
                    ? $opening + $net
                    : $opening + ($activity['credit'] - $activity['debit']);

                return ['account' => $account, 'balance' => round($balance, 2)];
            })
            ->filter(fn ($row) => abs($row['balance']) > 0.001);
    }
}
