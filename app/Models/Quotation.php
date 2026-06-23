<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    protected $fillable = [
        'quotation_no', 'branch_id', 'customer_id', 'quotation_date', 'valid_until',
        'status', 'subtotal', 'discount_amount', 'vat_amount', 'total_amount',
        'created_by', 'remarks',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
