<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoice extends Model
{
    protected $fillable = [
        'invoice_no', 'branch_id', 'customer_id', 'quotation_id', 'proforma_invoice_id',
        'pos_session_id', 'currency_id', 'exchange_rate', 'invoice_date', 'invoice_type',
        'source', 'status', 'subtotal', 'discount_amount', 'vat_amount', 'total_amount',
        'foreign_total', 'paid_amount', 'created_by', 'remarks', 'voided_at', 'void_reason',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'voided_at' => 'datetime',
        'exchange_rate' => 'decimal:6',
        'foreign_total' => 'decimal:2',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function proformaInvoice(): BelongsTo
    {
        return $this->belongsTo(ProformaInvoice::class);
    }

    public function posSession(): BelongsTo
    {
        return $this->belongsTo(PosSession::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
