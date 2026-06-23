<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockCountSession extends Model
{
    protected $fillable = [
        'count_no', 'branch_id', 'location_id', 'count_date', 'status',
        'created_by', 'posted_at', 'remarks',
    ];

    protected $casts = [
        'count_date' => 'date',
        'posted_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockCountItem::class);
    }
}
