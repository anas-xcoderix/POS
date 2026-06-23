<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\PublicHoliday;
use App\Models\Branch;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HrService
{
    public function __construct(
        private AccountingService $accounting,
        private SettingService $settings,
    ) {}

    public function saveAttendance(int $employeeId, string $date, string $status, ?string $checkIn = null, ?string $checkOut = null, ?string $notes = null): AttendanceRecord
    {
        return AttendanceRecord::updateOrCreate(
            ['employee_id' => $employeeId, 'attendance_date' => $date],
            [
                'status' => $status,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'notes' => $notes,
            ]
        );
    }

    public function generatePayroll(int $month, int $year, ?int $branchId = null, ?int $userId = null): PayrollRun
    {
        return DB::transaction(function () use ($month, $year, $branchId, $userId) {
            $existing = PayrollRun::where('period_month', $month)
                ->where('period_year', $year)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when(! $branchId, fn ($q) => $q->whereNull('branch_id'))
                ->first();

            if ($existing && $existing->status === 'posted') {
                throw new \RuntimeException(__('messages.hr.payroll_already_posted'));
            }

            if ($existing) {
                $existing->items()->delete();
                $run = $existing;
                $run->update(['status' => 'draft', 'posted_at' => null, 'payment_status' => 'unpaid', 'paid_at' => null]);
            } else {
                $run = PayrollRun::create([
                    'payroll_no' => $this->nextPayrollNo($month, $year),
                    'branch_id' => $branchId,
                    'period_month' => $month,
                    'period_year' => $year,
                    'status' => 'draft',
                    'payment_status' => 'unpaid',
                    'created_by' => $userId,
                ]);
            }

            $employees = Employee::where('is_active', true)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->get();

            $workingDays = $this->workingDaysInMonth($month, $year, $branchId);

            foreach ($employees as $employee) {
                $this->createPayrollItemForEmployee($run, $employee, $month, $year, $workingDays);
            }

            $this->recalculateRunTotal($run);

            return $run->fresh(['items.employee.department', 'branch']);
        });
    }

    public function updatePayrollItem(PayrollItem $item, array $data): PayrollItem
    {
        $run = $item->payrollRun;
        if ($run->status !== 'draft') {
            throw new \RuntimeException(__('messages.hr.payroll_not_draft'));
        }

        $item->update([
            'basic_salary' => $data['basic_salary'] ?? $item->basic_salary,
            'housing_allowance' => $data['housing_allowance'] ?? $item->housing_allowance,
            'transport_allowance' => $data['transport_allowance'] ?? $item->transport_allowance,
            'overtime_amount' => $data['overtime_amount'] ?? $item->overtime_amount,
            'bonus_amount' => $data['bonus_amount'] ?? $item->bonus_amount,
            'deductions' => $data['deductions'] ?? $item->deductions,
            'gosi_deduction' => $data['gosi_deduction'] ?? $item->gosi_deduction,
            'loan_deduction' => $data['loan_deduction'] ?? $item->loan_deduction,
            'other_deductions' => $data['other_deductions'] ?? $item->other_deductions,
            'days_present' => $data['days_present'] ?? $item->days_present,
            'days_absent' => $data['days_absent'] ?? $item->days_absent,
            'notes' => $data['notes'] ?? $item->notes,
        ]);

        $item->update(['net_pay' => $this->calculateNetPay($item->fresh())]);
        $this->recalculateRunTotal($run);

        return $item->fresh(['employee.department']);
    }

    public function postPayroll(PayrollRun $run): PayrollRun
    {
        if ($run->status === 'posted') {
            return $run;
        }

        return DB::transaction(function () use ($run) {
            $run->load('items');
            $total = round((float) $run->total_amount, 2);

            if ($total > 0 && $this->accounting->isAutoPostEnabled()) {
                $expense = $this->accounting->accountByCode($this->accounting->glCode('salary_expense'));
                $payable = $this->accounting->accountByCode($this->accounting->glCode('salaries_payable'));

                $this->accounting->postEntry(
                    [
                        'entry_date' => now()->toDateString(),
                        'description' => 'Payroll '.$run->payroll_no,
                        'branch_id' => $this->resolveBranchId($run->branch_id),
                    ],
                    [
                        ['account_id' => $expense->id, 'debit' => $total, 'credit' => 0, 'description' => 'Salaries expense'],
                        ['account_id' => $payable->id, 'debit' => 0, 'credit' => $total, 'description' => 'Salaries payable'],
                    ],
                    'payroll_run',
                    $run->id
                );
            }

            $run->update([
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            return $run->fresh();
        });
    }

    public function payPayroll(PayrollRun $run, string $paymentMethod = 'cash', ?string $reference = null): PayrollRun
    {
        if ($run->status !== 'posted') {
            throw new \RuntimeException(__('messages.hr.payroll_not_posted'));
        }

        if ($run->payment_status === 'paid') {
            throw new \RuntimeException(__('messages.hr.payroll_already_paid'));
        }

        return DB::transaction(function () use ($run, $paymentMethod, $reference) {
            $total = round((float) $run->total_amount, 2);

            if ($total > 0 && $this->accounting->isAutoPostEnabled()) {
                $payable = $this->accounting->accountByCode($this->accounting->glCode('salaries_payable'));
                $cashAccount = $this->accounting->accountByCode($this->accounting->glCode('cash'));

                $this->accounting->postEntry(
                    [
                        'entry_date' => now()->toDateString(),
                        'description' => 'Payroll payment '.$run->payroll_no,
                        'branch_id' => $this->resolveBranchId($run->branch_id),
                    ],
                    [
                        ['account_id' => $payable->id, 'debit' => $total, 'credit' => 0, 'description' => 'Clear salaries payable'],
                        ['account_id' => $cashAccount->id, 'debit' => 0, 'credit' => $total, 'description' => ucfirst($paymentMethod).' payment'],
                    ],
                    'payroll_payment',
                    $run->id
                );
            }

            $run->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'payment_reference' => $reference,
            ]);

            return $run->fresh();
        });
    }

    public function createLeaveRequest(array $data, ?int $userId = null): LeaveRequest
    {
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $days = $start->diffInDays($end) + 1;

        return LeaveRequest::create([
            'employee_id' => $data['employee_id'],
            'leave_type_id' => $data['leave_type_id'],
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'days' => $days,
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
            'created_by' => $userId,
        ]);
    }

    public function approveLeaveRequest(LeaveRequest $request, int $userId): LeaveRequest
    {
        if ($request->status !== 'pending') {
            throw new \RuntimeException(__('messages.hr.leave_not_pending'));
        }

        return DB::transaction(function () use ($request, $userId) {
            $request->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            foreach (CarbonPeriod::create($request->start_date, $request->end_date) as $date) {
                if ($date->isFriday()) {
                    continue;
                }
                $this->saveAttendance(
                    $request->employee_id,
                    $date->toDateString(),
                    'leave',
                    notes: 'Leave #'.$request->id
                );
            }

            return $request->fresh(['employee', 'leaveType']);
        });
    }

    public function leaveBalance(Employee $employee, int $leaveTypeId, ?int $year = null): array
    {
        $year = $year ?? (int) date('Y');
        $type = \App\Models\LeaveType::find($leaveTypeId);
        $used = (int) LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('days');

        $max = $type?->max_days_per_year ?? 0;

        return [
            'used' => $used,
            'max' => $max,
            'remaining' => max(0, $max - $used),
        ];
    }

    public function employeeAttendanceSummary(Employee $employee, int $month, int $year): array
    {
        return $this->attendanceStats($employee->id, $month, $year);
    }

    public function expiringDocuments(int $withinDays = 60): Collection
    {
        $until = now()->addDays($withinDays)->toDateString();

        return Employee::where('is_active', true)
            ->where(function ($q) use ($until) {
                $q->where(function ($q2) use ($until) {
                    $q2->whereNotNull('aqama_expiry')->where('aqama_expiry', '<=', $until);
                })->orWhere(function ($q2) use ($until) {
                    $q2->whereNotNull('license_expiry')->where('license_expiry', '<=', $until);
                });
            })
            ->with(['branch', 'department'])
            ->orderBy('aqama_expiry')
            ->orderBy('license_expiry')
            ->get();
    }

    public function expiringVehicles(int $withinDays = 60): Collection
    {
        return \App\Models\Vehicle::where('is_active', true)
            ->whereNotNull('istimara_expiry')
            ->where('istimara_expiry', '<=', now()->addDays($withinDays)->toDateString())
            ->with('customer')
            ->orderBy('istimara_expiry')
            ->get();
    }

    protected function createPayrollItemForEmployee(PayrollRun $run, Employee $employee, int $month, int $year, int $workingDays): PayrollItem
    {
        $stats = $this->attendanceStats($employee->id, $month, $year);
        $daysPresent = $stats['present'] + ($stats['half_day'] * 0.5);
        $daysAbsent = $stats['absent'];
        $daysLeave = $stats['leave'];

        $earnedBasic = $workingDays > 0
            ? round((float) $employee->basic_salary * ($daysPresent / $workingDays), 2)
            : 0;

        $absentDeduction = $workingDays > 0
            ? round((float) $employee->basic_salary / $workingDays * $daysAbsent, 2)
            : 0;

        $housing = (float) $employee->housing_allowance;
        $transport = (float) $employee->transport_allowance;

        $overtimeHours = $this->overtimeHoursFromAttendance($employee->id, $month, $year);
        $overtimeAmount = round($overtimeHours * (float) $employee->overtime_rate, 2);

        $gosiDeduction = $employee->gosi_eligible
            ? $this->calculateGosiDeduction($earnedBasic + $housing)
            : 0;

        $item = PayrollItem::create([
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'basic_salary' => $earnedBasic,
            'housing_allowance' => $housing,
            'transport_allowance' => $transport,
            'overtime_amount' => $overtimeAmount,
            'bonus_amount' => 0,
            'days_present' => (int) floor($daysPresent),
            'days_absent' => (int) ($daysAbsent + $daysLeave),
            'deductions' => $absentDeduction,
            'gosi_deduction' => $gosiDeduction,
            'loan_deduction' => 0,
            'other_deductions' => 0,
        ]);

        $item->update(['net_pay' => $this->calculateNetPay($item)]);

        return $item;
    }

    protected function calculateNetPay(PayrollItem $item): float
    {
        $gross = (float) $item->basic_salary
            + (float) $item->housing_allowance
            + (float) $item->transport_allowance
            + (float) $item->overtime_amount
            + (float) $item->bonus_amount;

        $totalDeductions = (float) $item->deductions
            + (float) $item->gosi_deduction
            + (float) $item->loan_deduction
            + (float) $item->other_deductions;

        return round(max(0, $gross - $totalDeductions), 2);
    }

    protected function calculateGosiDeduction(float $gosiBase): float
    {
        $rate = (float) $this->settings->get('gosi_employee_rate', config('erp.default_settings.gosi_employee_rate', 9.75));

        return round($gosiBase * ($rate / 100), 2);
    }

    protected function overtimeHoursFromAttendance(int $employeeId, int $month, int $year): float
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $records = AttendanceRecord::where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('check_in')
            ->whereNotNull('check_out')
            ->get();

        $hours = 0;
        foreach ($records as $record) {
            $in = Carbon::parse($record->check_in);
            $out = Carbon::parse($record->check_out);
            $worked = $out->diffInMinutes($in) / 60;
            $standard = 8;
            if ($worked > $standard) {
                $hours += $worked - $standard;
            }
        }

        return round($hours, 2);
    }

    protected function recalculateRunTotal(PayrollRun $run): void
    {
        $total = (float) $run->items()->sum('net_pay');
        $run->update(['total_amount' => round($total, 2)]);
    }

    protected function attendanceStats(int $employeeId, int $month, int $year): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $records = AttendanceRecord::where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        return [
            'present' => $records->where('status', 'present')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'leave' => $records->where('status', 'leave')->count(),
            'half_day' => $records->where('status', 'half_day')->count(),
        ];
    }

    protected function workingDaysInMonth(int $month, int $year, ?int $branchId = null): int
    {
        $start = Carbon::create($year, $month, 1);
        $days = $start->daysInMonth;
        $holidays = $this->holidayDates($month, $year, $branchId);
        $working = 0;

        for ($d = 1; $d <= $days; $d++) {
            $date = Carbon::create($year, $month, $d);
            if ($date->isFriday()) {
                continue;
            }
            if ($holidays->contains($date->toDateString())) {
                continue;
            }
            $working++;
        }

        return max($working, 1);
    }

    protected function holidayDates(int $month, int $year, ?int $branchId): Collection
    {
        return PublicHoliday::where('is_active', true)
            ->whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id');
                if ($branchId) {
                    $q->orWhere('branch_id', $branchId);
                }
            })
            ->pluck('holiday_date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString());
    }

    protected function resolveBranchId(?int $branchId): int
    {
        return $branchId ?? (int) Branch::query()->value('id');
    }

    protected function nextPayrollNo(int $month, int $year): string
    {
        return sprintf('PR-%04d%02d-%s', $year, $month, str_pad((string) (PayrollRun::count() + 1), 3, '0', STR_PAD_LEFT));
    }
}
