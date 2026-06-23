<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use App\Services\BranchScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalEntryController extends Controller
{
    public function __construct(
        private BranchScopeService $branchScope,
        private AccountingService $accountingService,
    ) {}

    public function index(Request $request): View
    {
        $query = JournalEntry::with(['branch', 'creator'])->latest('entry_date');

        $this->branchScope->apply($query);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('entry_no', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return view('finance.journals.index', [
            'records' => $query->paginate(20)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('finance.journals.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'accounts' => Account::where('is_active', true)->orderBy('account_code')->get(),
            'entryNo' => 'JE-M-'.now()->format('Ymd').'-'.str_pad((string) (JournalEntry::count() + 1), 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'entry_no' => 'nullable|string',
            'branch_id' => 'required|exists:branches,id',
            'entry_date' => 'required|date',
            'description' => 'required|string|max:500',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:255',
        ]);

        $lines = collect($data['lines'])->map(fn ($line) => [
            'account_id' => (int) $line['account_id'],
            'debit' => (float) ($line['debit'] ?? 0),
            'credit' => (float) ($line['credit'] ?? 0),
            'description' => $line['description'] ?? null,
        ])->filter(fn ($l) => $l['debit'] > 0 || $l['credit'] > 0)->values()->all();

        try {
            $this->accountingService->postManualJournal([
                'branch_id' => $data['branch_id'],
                'entry_date' => $data['entry_date'],
                'description' => $data['description'],
            ], $lines, auth()->id());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('journal-entries.index')->with('success', 'Manual journal posted.');
    }

    public function show(JournalEntry $journalEntry): View
    {
        $journalEntry->load(['lines.account', 'branch', 'creator']);

        return view('finance.journals.show', ['entry' => $journalEntry]);
    }
}
