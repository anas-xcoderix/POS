<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCountItem extends Model
{
    protected $fillable = [
        'stock_count_session_id', 'part_id', 'location_id',
        'system_qty', 'counted_qty', 'variance',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(StockCountSession::class, 'stock_count_session_id');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
