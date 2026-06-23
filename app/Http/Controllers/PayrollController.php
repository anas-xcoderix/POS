<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\PayrollItem;
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

        return redirect()->route('payroll.show', $run)->with('success', __('messages.hr.payroll_generated'));
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

        return back()->with('success', __('messages.hr.payroll_posted'));
    }

    public function pay(Request $request, PayrollRun $payroll): RedirectResponse
    {
        $data = $request->validate([
            'payment_method' => 'required|in:cash,bank_transfer',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        try {
            $this->hr->payPayroll($payroll, $data['payment_method'], $data['payment_reference'] ?? null);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('messages.hr.payroll_paid'));
    }

    public function updateItem(Request $request, PayrollRun $payroll, PayrollItem $item): RedirectResponse
    {
        abort_unless($item->payroll_run_id === $payroll->id, 404);

        $data = $request->validate([
            'basic_salary' => 'nullable|numeric|min:0',
            'housing_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'overtime_amount' => 'nullable|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'gosi_deduction' => 'nullable|numeric|min:0',
            'loan_deduction' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'days_present' => 'nullable|integer|min:0',
            'days_absent' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $this->hr->updatePayrollItem($item, $data);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('messages.hr.payroll_line_updated'));
    }

    public function regenerate(PayrollRun $payroll): RedirectResponse
    {
        if ($payroll->status !== 'draft') {
            return back()->with('error', __('messages.hr.payroll_not_draft'));
        }

        try {
            $run = $this->hr->generatePayroll(
                $payroll->period_month,
                $payroll->period_year,
                $payroll->branch_id,
                auth()->id()
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('payroll.show', $run)->with('success', __('messages.hr.payroll_regenerated'));
    }
}
