<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleOrder extends Model
{
    protected $fillable = [
        'order_no', 'customer_id', 'branch_id', 'order_date',
        'vehicle_make', 'vehicle_model', 'status', 'estimated_amount', 'remarks',
    ];

    protected $casts = [
        'order_date' => 'date',
        'estimated_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
