<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HrService
{
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
                throw new \RuntimeException('Payroll for this period is already posted.');
            }

            if ($existing) {
                $existing->items()->delete();
                $run = $existing;
            } else {
                $run = PayrollRun::create([
                    'payroll_no' => $this->nextPayrollNo($month, $year),
                    'branch_id' => $branchId,
                    'period_month' => $month,
                    'period_year' => $year,
                    'status' => 'draft',
                    'created_by' => $userId,
                ]);
            }

            $employees = Employee::where('is_active', true)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->get();

            $workingDays = $this->workingDaysInMonth($month, $year);
            $totalNet = 0;

            foreach ($employees as $employee) {
                $stats = $this->attendanceStats($employee->id, $month, $year);
                $daysPresent = $stats['present'] + ($stats['half_day'] * 0.5);
                $daysAbsent = $stats['absent'] + $stats['leave'];

                $earnedBasic = $workingDays > 0
                    ? round((float) $employee->basic_salary * ($daysPresent / $workingDays), 2)
                    : 0;

                $absentDeduction = $workingDays > 0
                    ? round((float) $employee->basic_salary / $workingDays * $daysAbsent, 2)
                    : 0;

                $housing = (float) $employee->housing_allowance;
                $transport = (float) $employee->transport_allowance;
                $deductions = round($absentDeduction, 2);
                $netPay = round($earnedBasic + $housing + $transport - $deductions, 2);

                PayrollItem::create([
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $earnedBasic,
                    'housing_allowance' => $housing,
                    'transport_allowance' => $transport,
                    'days_present' => (int) floor($daysPresent),
                    'days_absent' => (int) $daysAbsent,
                    'deductions' => $deductions,
                    'net_pay' => max(0, $netPay),
                ]);

                $totalNet += max(0, $netPay);
            }

            $run->update(['total_amount' => round($totalNet, 2)]);

            return $run->fresh(['items.employee', 'branch']);
        });
    }

    public function postPayroll(PayrollRun $run): PayrollRun
    {
        if ($run->status === 'posted') {
            return $run;
        }

        $run->update([
            'status' => 'posted',
            'posted_at' => now(),
        ]);

        return $run->fresh();
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

    protected function workingDaysInMonth(int $month, int $year): int
    {
        $start = Carbon::create($year, $month, 1);
        $days = $start->daysInMonth;
        $working = 0;

        for ($d = 1; $d <= $days; $d++) {
            $date = Carbon::create($year, $month, $d);
            if (! $date->isFriday()) {
                $working++;
            }
        }

        return max($working, 1);
    }

    protected function nextPayrollNo(int $month, int $year): string
    {
        return sprintf('PR-%04d%02d-%s', $year, $month, str_pad((string) (PayrollRun::count() + 1), 3, '0', STR_PAD_LEFT));
    }
}
