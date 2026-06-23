<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBalance extends Model
{
    protected $fillable = [
        'branch_id', 'location_id', 'part_id', 'quantity', 'reserved_qty', 'avg_cost',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
