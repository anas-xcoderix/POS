<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleReturn extends Model
{
    protected $fillable = [
        'return_no', 'sales_invoice_id', 'branch_id', 'customer_id', 'return_date',
        'status', 'total_amount', 'created_by', 'remarks',
    ];

    protected $casts = ['return_date' => 'date'];

    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }
}
