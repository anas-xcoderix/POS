<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'employee_no', 'branch_id', 'department_id', 'user_id',
        'name', 'name_ar', 'phone', 'email', 'hire_date',
        'basic_salary', 'housing_allowance', 'transport_allowance',
        'gosi_eligible', 'gosi_number', 'bank_name', 'bank_account', 'overtime_rate',
        'job_title', 'aqama_no', 'aqama_expiry', 'license_no', 'license_expiry',
        'is_active',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'aqama_expiry' => 'date',
        'license_expiry' => 'date',
        'basic_salary' => 'decimal:2',
        'housing_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'gosi_eligible' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
