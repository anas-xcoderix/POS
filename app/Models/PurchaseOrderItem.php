<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'part_id', 'quantity', 'unit_price', 'received_qty', 'line_total',
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
