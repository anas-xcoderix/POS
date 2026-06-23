<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_run_id', 'employee_id', 'basic_salary', 'housing_allowance',
        'transport_allowance', 'overtime_amount', 'bonus_amount',
        'days_present', 'days_absent', 'deductions', 'gosi_deduction',
        'loan_deduction', 'other_deductions', 'net_pay', 'notes',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'housing_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'deductions' => 'decimal:2',
        'gosi_deduction' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function totalAllowances(): float
    {
        return (float) $this->housing_allowance + (float) $this->transport_allowance + (float) $this->overtime_amount + (float) $this->bonus_amount;
    }

    public function totalDeductions(): float
    {
        return (float) $this->deductions + (float) $this->gosi_deduction + (float) $this->loan_deduction + (float) $this->other_deductions;
    }
}
