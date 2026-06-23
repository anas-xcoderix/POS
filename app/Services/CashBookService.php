<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CashBookEntry;
use Illuminate\Support\Facades\DB;

class CashBookService
{
    public function __construct(
        private AuditService $audit,
        private CurrencyService $currencyService,
    ) {}

    public function recordEntry(array $data): CashBookEntry
    {
        return DB::transaction(function () use ($data) {
            $lastBalance = CashBookEntry::where('branch_id', $data['branch_id'])
                ->where('account_id', $data['account_id'])
                ->orderByDesc('entry_date')
                ->orderByDesc('id')
                ->value('running_balance') ?? 0;

            $amount = (float) $data['amount'];
            $isReceipt = in_array($data['entry_type'], ['receipt', 'in'], true);
            $running = $isReceipt ? $lastBalance + $amount : $lastBalance - $amount;

            if (! empty($data['currency_id']) && empty($data['foreign_amount'])) {
                $data['foreign_amount'] = $amount;
                $data['exchange_rate'] = $data['exchange_rate'] ?? 1;
            }

            $entry = CashBookEntry::create(array_merge($data, [
                'running_balance' => $running,
                'entry_no' => $data['entry_no'] ?? $this->nextEntryNo(),
            ]));

            $this->audit->log('cashbook.entry', $entry, null, $entry->toArray(), $entry->entry_no);

            return $entry;
        });
    }

    public function balanceForBranch(int $branchId, ?int $accountId = null): float
    {
        $accountId ??= Account::where('code', config('erp.gl_accounts.cash'))->value('id');

        return (float) CashBookEntry::where('branch_id', $branchId)
            ->where('account_id', $accountId)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->value('running_balance') ?? 0;
    }

    public function entriesForPeriod(int $branchId, string $from, string $to, ?int $accountId = null)
    {
        $query = CashBookEntry::with(['account', 'currency'])
            ->where('branch_id', $branchId)
            ->whereBetween('entry_date', [$from, $to])
            ->orderBy('entry_date')
            ->orderBy('id');

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        return $query->get();
    }

    protected function nextEntryNo(): string
    {
        return 'CB-'.now()->format('Ymd').'-'.str_pad((string) (CashBookEntry::count() + 1), 4, '0', STR_PAD_LEFT);
    }
}
