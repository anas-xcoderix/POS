<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Services\HrService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private HrService $hr) {}

    public function index(Request $request): View
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $employees = Employee::where('is_active', true)->with('department')->orderBy('name')->get();
        $records = AttendanceRecord::whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($r) => $r->employee_id.'|'.$r->attendance_date->format('Y-m-d'));

        return view('hr.attendance.index', compact('employees', 'records', 'month', 'year', 'start', 'end'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'entries' => 'required|array',
            'entries.*.employee_id' => 'required|exists:employees,id',
            'entries.*.attendance_date' => 'required|date',
            'entries.*.status' => 'required|in:present,absent,leave,half_day',
            'entries.*.check_in' => 'nullable|date_format:H:i',
            'entries.*.check_out' => 'nullable|date_format:H:i',
            'entries.*.notes' => 'nullable|string|max:255',
        ]);

        foreach ($data['entries'] as $entry) {
            $this->hr->saveAttendance(
                (int) $entry['employee_id'],
                $entry['attendance_date'],
                $entry['status'],
                $entry['check_in'] ?? null,
                $entry['check_out'] ?? null,
                $entry['notes'] ?? null,
            );
        }

        return redirect()->route('attendance.index', [
            'month' => $data['month'],
            'year' => $data['year'],
        ])->with('success', __('messages.hr.attendance_saved'));
    }
}
