<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShowroomColor extends Model
{
    protected $fillable = ['code', 'name', 'name_ar', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function vehicles(): HasMany
    {
        return $this->hasMany(ShowroomVehicle::class, 'color_id');
    }
}
