<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cheque extends Model
{
    protected $fillable = [
        'cheque_no', 'cheque_type', 'customer_id', 'vendor_id', 'bank_account_id',
        'branch_id', 'cheque_date', 'due_date', 'amount', 'status', 'bank_name',
        'remarks', 'created_by',
    ];

    protected $casts = [
        'cheque_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
