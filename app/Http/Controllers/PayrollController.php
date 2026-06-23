<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\PayrollRun;
use App\Services\HrService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function __construct(private HrService $hr) {}

    public function index(): View
    {
        return view('hr.payroll.index', [
            'records' => PayrollRun::with(['branch', 'creator'])->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('hr.payroll.create', [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2000|max:2100',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        try {
            $run = $this->hr->generatePayroll(
                (int) $data['period_month'],
                (int) $data['period_year'],
                $data['branch_id'] ?? null,
                auth()->id()
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('payroll.show', $run)->with('success', __('messages.payroll.generated'));
    }

    public function show(PayrollRun $payroll): View
    {
        $payroll->load(['items.employee.department', 'branch', 'creator']);

        return view('hr.payroll.show', ['run' => $payroll]);
    }

    public function post(PayrollRun $payroll): RedirectResponse
    {
        try {
            $this->hr->postPayroll($payroll);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('messages.payroll.posted'));
    }
}
