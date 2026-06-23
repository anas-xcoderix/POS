<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Branch;
use App\Models\CashBookEntry;
use App\Models\Currency;
use App\Services\BranchScopeService;
use App\Services\CashBookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashBookController extends Controller
{
    public function __construct(
        private CashBookService $cashBookService,
        private BranchScopeService $branchScope,
    ) {}

    public function index(Request $request): View
    {
        $query = CashBookEntry::with(['branch', 'account', 'currency'])->latest('entry_date');

        $this->branchScope->apply($query);

        if ($branchId = $request->get('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        if ($from = $request->get('from')) {
            $query->where('entry_date', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->where('entry_date', '<=', $to);
        }

        return view('cash-book.index', [
            'records' => $query->paginate(20)->withQueryString(),
            'branches' => Branch::where('is_active', true)->get(),
            'branchId' => $branchId,
            'from' => $from ?? now()->startOfMonth()->toDateString(),
            'to' => $to ?? now()->toDateString(),
        ]);
    }

    public function create(): View
    {
        return view('cash-book.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'accounts' => Account::where('is_active', true)->orderBy('code')->get(),
            'currencies' => Currency::where('is_active', true)->get(),
            'defaultBranchId' => $this->branchScope->defaultBranchId(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'entry_date' => 'required|date',
            'entry_type' => 'required|string|in:receipt,payment,in,out',
            'account_id' => 'required|exists:accounts,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'exchange_rate' => 'nullable|numeric|min:0',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'reference_no' => 'nullable|string|max:100',
        ]);

        $data['created_by'] = auth()->id();

        try {
            $this->cashBookService->recordEntry($data);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('cash-book.index')->with('success', 'Cash book entry recorded.');
    }
}
