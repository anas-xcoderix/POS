<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'code', 'name', 'name_ar', 'phone', 'email', 'address',
        'is_active', 'is_head_office',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_head_office' => 'boolean',
    ];

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
