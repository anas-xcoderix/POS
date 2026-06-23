<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShowroomVehicleTransfer extends Model
{
    protected $fillable = [
        'transfer_no', 'showroom_vehicle_id', 'from_branch_id', 'to_branch_id',
        'transfer_date', 'status', 'received_at', 'created_by', 'remarks',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'received_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(ShowroomVehicle::class, 'showroom_vehicle_id');
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }
}
