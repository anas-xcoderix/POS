<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportDriver extends Model
{
    protected $fillable = [
        'code', 'name', 'name_ar', 'phone', 'license_no', 'vehicle_plate',
        'branch_id', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(TransportShipment::class);
    }
}
