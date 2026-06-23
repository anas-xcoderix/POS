<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_no', 'branch_id', 'vendor_id', 'po_date', 'expected_date',
        'status', 'subtotal', 'vat_amount', 'total_amount', 'created_by', 'remarks',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function purchaseInvoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function pendingQty(): float
    {
        return (float) $this->items->sum(fn ($i) => max(0, (float) $i->quantity - (float) $i->received_qty));
    }
}
