<?php

namespace App\Http\Controllers;

use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceReportController extends Controller
{
    public function __construct(private AccountingService $accounting) {}

    public function index(): View
    {
        return view('finance.reports.index');
    }

    public function trialBalance(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $rows = $this->accounting->trialBalance($from, $to);
        $totalDebit = $rows->sum('debit');
        $totalCredit = $rows->sum('credit');

        return view('finance.reports.trial-balance', compact('rows', 'from', 'to', 'totalDebit', 'totalCredit'));
    }

    public function incomeStatement(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->accounting->incomeStatement($from, $to);

        return view('finance.reports.income-statement', $data);
    }

    public function balanceSheet(Request $request): View
    {
        $asOf = $request->get('as_of', now()->toDateString());
        $data = $this->accounting->balanceSheet($asOf);

        return view('finance.reports.balance-sheet', $data);
    }

    public function customerAging(): View
    {
        return view('finance.reports.customer-aging', [
            'records' => $this->accounting->customerAging(),
        ]);
    }

    public function vendorAging(): View
    {
        return view('finance.reports.vendor-aging', [
            'records' => $this->accounting->vendorAging(),
        ]);
    }
}
