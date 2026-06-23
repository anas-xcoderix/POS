<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShowroomVehicleModel extends Model
{
    protected $fillable = ['code', 'name', 'name_ar', 'franchise_id', 'make', 'model_year', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(ShowroomVehicle::class, 'model_id');
    }
}
