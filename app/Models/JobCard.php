<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobCard extends Model
{
    protected $fillable = [
        'job_no', 'branch_id', 'customer_id', 'vehicle_id', 'location_id',
        'job_date', 'promised_date', 'status', 'labor_total', 'parts_total',
        'total_amount', 'mechanic_id', 'sales_invoice_id', 'created_by',
        'complaint', 'remarks',
    ];

    protected $casts = [
        'job_date' => 'date',
        'promised_date' => 'date',
        'labor_total' => 'decimal:2',
        'parts_total' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'mechanic_id');
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(JobCardItem::class);
    }

    public function isWip(): bool
    {
        return in_array($this->status, ['open', 'in_progress'], true);
    }
}
