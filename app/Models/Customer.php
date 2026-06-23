<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'code', 'branch_id', 'name', 'name_ar', 'contact_person', 'phone', 'mobile',
        'email', 'address', 'city', 'country', 'vat_no', 'customer_type',
        'price_level', 'discount_percent', 'credit_limit', 'balance', 'payment_terms_days', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function availableCredit(): ?float
    {
        return app(\App\Services\CreditService::class)->availableCredit($this);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }
}
