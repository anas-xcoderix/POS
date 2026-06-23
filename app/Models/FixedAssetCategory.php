<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedAssetCategory extends Model
{
    protected $fillable = [
        'code', 'name', 'name_ar', 'default_life_months',
        'asset_account_id', 'depreciation_account_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'asset_account_id');
    }

    public function depreciationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'depreciation_account_id');
    }

    public function fixedAssets(): HasMany
    {
        return $this->hasMany(FixedAsset::class, 'category_id');
    }
}
