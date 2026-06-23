<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountRule extends Model
{
    protected $fillable = [
        'name', 'rule_type', 'customer_id', 'brand_id', 'customer_type',
        'discount_percent', 'price_level', 'priority', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}
