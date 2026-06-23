<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    protected $fillable = [
        'payroll_no', 'branch_id', 'period_month', 'period_year',
        'status', 'total_amount', 'created_by', 'posted_at',
        'payment_status', 'paid_at', 'payment_reference',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'posted_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function periodLabel(): string
    {
        return date('F Y', mktime(0, 0, 0, $this->period_month, 1, $this->period_year));
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }
}
