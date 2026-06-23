<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShowroomVehicle extends Model
{
    protected $fillable = [
        'stock_no', 'branch_id', 'model_id', 'color_id', 'franchise_id',
        'chassis_no', 'engine_no', 'year', 'purchase_cost', 'list_price',
        'status', 'received_date', 'sold_date', 'customer_id', 'sales_invoice_id',
        'remarks', 'created_by',
    ];

    protected $casts = [
        'received_date' => 'date',
        'sold_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'list_price' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(ShowroomVehicleModel::class, 'model_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(ShowroomColor::class, 'color_id');
    }

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(ShowroomVehicleTransfer::class);
    }
}
