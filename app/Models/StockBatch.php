<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBatch extends Model
{
    protected $fillable = [
        'branch_id', 'location_id', 'part_id', 'batch_no', 'lot_no', 'serial_no',
        'expiry_date', 'quantity', 'unit_cost', 'received_date',
        'reference_type', 'reference_id', 'reference_no',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'received_date' => 'date',
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:4',
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
