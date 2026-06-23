<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportShipment extends Model
{
    public const STATUSES = ['pending', 'dispatched', 'in_transit', 'delivered', 'failed', 'cancelled'];

    protected $fillable = [
        'shipment_no', 'branch_id', 'customer_id', 'transport_driver_id',
        'delivery_note_id', 'sales_invoice_id', 'ship_date', 'expected_date', 'delivered_at',
        'status', 'ship_to_address', 'contact_phone', 'transport_charge', 'cod_amount',
        'cod_collected', 'cod_settled', 'vehicle_plate', 'remarks', 'created_by', 'dispatched_at',
    ];

    protected $casts = [
        'ship_date' => 'date',
        'expected_date' => 'date',
        'delivered_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'transport_charge' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'cod_collected' => 'decimal:2',
        'cod_settled' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(TransportDriver::class, 'transport_driver_id');
    }

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cashVoucherItems(): HasMany
    {
        return $this->hasMany(TransportCashVoucherItem::class);
    }

    public function codOutstanding(): float
    {
        return max(0, (float) $this->cod_amount - (float) $this->cod_collected);
    }
}
