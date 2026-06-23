<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoice extends Model
{
    protected $fillable = [
        'invoice_no', 'branch_id', 'customer_id', 'quotation_id', 'invoice_date',
        'invoice_type', 'status', 'subtotal', 'discount_amount', 'vat_amount',
        'total_amount', 'paid_amount', 'created_by', 'remarks',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }
}
