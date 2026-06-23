<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Part extends Model
{
    protected $fillable = [
        'part_number', 'oem_no', 'manufacturer_part_no', 'barcode',
        'brand_id', 'origin_id', 'franchise_id', 'default_location_id',
        'description_en', 'description_ar', 'type_code', 'group_code', 'subgroup_code',
        'gn', 'is_kit', 'is_returnable', 'list_price', 'price_2', 'price_3', 'cost_price',
        'sale_pack_qty', 'purchase_pack_qty', 'hs_code', 'weight', 'vat_code',
        'min_stock', 'max_stock', 'remarks', 'is_active', 'track_batch', 'track_serial',
    ];

    protected $casts = [
        'is_kit' => 'boolean',
        'is_returnable' => 'boolean',
        'is_active' => 'boolean',
        'track_batch' => 'boolean',
        'track_serial' => 'boolean',
        'list_price' => 'decimal:4',
        'price_2' => 'decimal:4',
        'price_3' => 'decimal:4',
        'cost_price' => 'decimal:4',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Origin::class);
    }

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function defaultLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'default_location_id');
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }

    public function kitsAsKit(): HasMany
    {
        return $this->hasMany(PartKit::class, 'kit_part_id');
    }

    public function alternatives(): HasMany
    {
        return $this->hasMany(PartAlternative::class);
    }
}
