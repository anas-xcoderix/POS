<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id', 'part_id', 'quantity', 'unit_price',
        'discount_percent', 'vat_percent', 'line_total',
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
