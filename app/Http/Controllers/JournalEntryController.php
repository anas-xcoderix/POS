<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Services\BranchScopeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalEntryController extends Controller
{
    public function __construct(private BranchScopeService $branchScope) {}

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

    public function show(JournalEntry $journalEntry): View
    {
        $journalEntry->load(['lines.account', 'branch', 'creator']);

        return view('finance.journals.show', ['entry' => $journalEntry]);
    }
}
