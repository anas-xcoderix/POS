<?php

namespace App\Http\Controllers;

use App\Models\FiscalPeriod;
use App\Services\FiscalPeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FiscalPeriodController extends Controller
{
    public function __construct(private FiscalPeriodService $fiscalPeriodService) {}

    public function index(): View
    {
        $periods = FiscalPeriod::orderByDesc('year')->orderByDesc('month')->paginate(24);

        return view('finance.periods.index', ['records' => $periods]);
    }

    public function close(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $this->fiscalPeriodService->close((int) $data['year'], (int) $data['month'], auth()->id());

        return back()->with('success', __('messages.fiscal.closed'));
    }

    public function reopen(FiscalPeriod $fiscalPeriod): RedirectResponse
    {
        $this->fiscalPeriodService->reopen($fiscalPeriod->year, $fiscalPeriod->month);

        return back()->with('success', __('messages.fiscal.reopened'));
    }
}
