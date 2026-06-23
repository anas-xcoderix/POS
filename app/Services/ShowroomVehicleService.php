<?php

namespace App\Services;

use App\Models\ShowroomVehicle;
use App\Models\ShowroomVehicleTransfer;
use Illuminate\Support\Facades\DB;

class ShowroomVehicleService
{
    public function __construct(private AuditService $audit) {}

    public function register(array $data): ShowroomVehicle
    {
        return DB::transaction(function () use ($data) {
            $vehicle = ShowroomVehicle::create($data);
            $this->audit->log('showroom.vehicle_received', $vehicle, null, $vehicle->toArray(), $vehicle->stock_no);

            return $vehicle->fresh(['model', 'color', 'branch']);
        });
    }

    public function transfer(ShowroomVehicle $vehicle, int $toBranchId, ?int $userId = null, ?string $remarks = null): ShowroomVehicleTransfer
    {
        return DB::transaction(function () use ($vehicle, $toBranchId, $userId, $remarks) {
            if ($vehicle->status !== 'in_stock') {
                throw new \RuntimeException('Only in-stock vehicles can be transferred.');
            }

            $transferNo = 'SVT-'.now()->format('Ymd').'-'.str_pad((string) (ShowroomVehicleTransfer::count() + 1), 4, '0', STR_PAD_LEFT);

            $transfer = ShowroomVehicleTransfer::create([
                'transfer_no' => $transferNo,
                'showroom_vehicle_id' => $vehicle->id,
                'from_branch_id' => $vehicle->branch_id,
                'to_branch_id' => $toBranchId,
                'transfer_date' => now()->toDateString(),
                'status' => 'pending',
                'created_by' => $userId,
                'remarks' => $remarks,
            ]);

            $vehicle->update(['status' => 'in_transit']);
            $this->audit->log('showroom.transfer_created', $transfer, null, $transfer->toArray(), $transferNo);

            return $transfer;
        });
    }

    public function receiveTransfer(ShowroomVehicleTransfer $transfer, ?int $userId = null): ShowroomVehicleTransfer
    {
        return DB::transaction(function () use ($transfer, $userId) {
            $transfer->load('vehicle');
            $transfer->vehicle->update([
                'branch_id' => $transfer->to_branch_id,
                'status' => 'in_stock',
            ]);
            $transfer->update(['status' => 'received', 'received_at' => now()]);
            $this->audit->log('showroom.transfer_received', $transfer, null, ['status' => 'received'], $transfer->transfer_no);

            return $transfer->fresh(['vehicle', 'fromBranch', 'toBranch']);
        });
    }

    public function markSold(ShowroomVehicle $vehicle, int $customerId, ?int $salesInvoiceId = null): ShowroomVehicle
    {
        return DB::transaction(function () use ($vehicle, $customerId, $salesInvoiceId) {
            if ($vehicle->status !== 'in_stock') {
                throw new \RuntimeException('Vehicle is not available for sale.');
            }

            $vehicle->update([
                'status' => 'sold',
                'sold_date' => now()->toDateString(),
                'customer_id' => $customerId,
                'sales_invoice_id' => $salesInvoiceId,
            ]);

            $this->audit->log('showroom.vehicle_sold', $vehicle, null, ['customer_id' => $customerId], $vehicle->stock_no);

            return $vehicle->fresh();
        });
    }
}
