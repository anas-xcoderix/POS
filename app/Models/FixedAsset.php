<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedAsset extends Model
{
    protected $fillable = [
        'asset_code', 'category_id', 'branch_id', 'location_id', 'name', 'name_ar',
        'purchase_date', 'purchase_value', 'salvage_value', 'useful_life_months',
        'depreciation_method', 'accumulated_depreciation', 'net_book_value',
        'status', 'disposed_at', 'remarks', 'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'disposed_at' => 'date',
        'purchase_value' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'net_book_value' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(FixedAssetCategory::class, 'category_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function depreciations(): HasMany
    {
        return $this->hasMany(FixedAssetDepreciation::class);
    }
}
