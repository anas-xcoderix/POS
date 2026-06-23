<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesInvoiceItem extends Model
{
    protected $fillable = [
        'sales_invoice_id', 'part_id', 'location_id', 'quantity',
        'unit_price', 'unit_cost', 'discount_percent', 'vat_percent', 'line_total',
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }
}
