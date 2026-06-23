<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'branch_id', 'location_id', 'part_id', 'movement_type', 'reference_type',
        'reference_id', 'reference_no', 'quantity_in', 'quantity_out', 'unit_cost',
        'balance_after', 'user_id', 'remarks', 'movement_date',
    ];

    protected $casts = ['movement_date' => 'datetime'];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
