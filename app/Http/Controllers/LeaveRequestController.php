<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\HrService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function __construct(private HrService $hr) {}

    public function index(): View
    {
        return view('hr.leave.index', [
            'records' => LeaveRequest::with(['employee', 'leaveType', 'approver'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('hr.leave.create', [
            'employees' => Employee::where('is_active', true)->orderBy('name')->get(),
            'leaveTypes' => LeaveType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        $this->hr->createLeaveRequest($data, auth()->id());

        return redirect()->route('leave.index')->with('success', __('messages.hr.leave_created'));
    }

    public function approve(LeaveRequest $leave): RedirectResponse
    {
        try {
            $this->hr->approveLeaveRequest($leave, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('messages.hr.leave_approved'));
    }

    public function reject(LeaveRequest $leave): RedirectResponse
    {
        $leave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', __('messages.hr.leave_rejected'));
    }
}
