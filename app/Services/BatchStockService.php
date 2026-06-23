<?php

namespace App\Services;

use App\Models\Part;
use App\Models\StockBatch;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class BatchStockService
{
    public function __construct(private StockService $stockService) {}

    public function receiveBatch(
        int $branchId,
        int $locationId,
        int $partId,
        float $quantity,
        float $unitCost,
        string $batchNo,
        ?string $lotNo = null,
        ?string $serialNo = null,
        ?\DateTimeInterface $expiryDate = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $referenceNo = null,
    ): StockBatch {
        return DB::transaction(function () use (
            $branchId, $locationId, $partId, $quantity, $unitCost, $batchNo,
            $lotNo, $serialNo, $expiryDate, $referenceType, $referenceId, $referenceNo
        ) {
            $batch = StockBatch::firstOrCreate(
                [
                    'branch_id' => $branchId,
                    'location_id' => $locationId,
                    'part_id' => $partId,
                    'batch_no' => $batchNo,
                    'serial_no' => $serialNo,
                ],
                [
                    'lot_no' => $lotNo,
                    'expiry_date' => $expiryDate,
                    'quantity' => 0,
                    'unit_cost' => $unitCost,
                    'received_date' => now()->toDateString(),
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'reference_no' => $referenceNo,
                ]
            );

            $batch->increment('quantity', $quantity);
            $this->stockService->receivePurchase(
                $branchId, $locationId, $partId, $quantity, $unitCost,
                $referenceId ?? 0, $referenceNo ?? $batchNo
            );

            StockMovement::where('part_id', $partId)
                ->where('branch_id', $branchId)
                ->where('location_id', $locationId)
                ->latest('id')
                ->limit(1)
                ->update(['stock_batch_id' => $batch->id]);

            return $batch->fresh();
        });
    }

    public function issueFifo(
        int $branchId,
        int $locationId,
        int $partId,
        float $quantity,
        int $referenceId,
        string $referenceNo,
        ?int $userId = null,
    ): void {
        $part = Part::findOrFail($partId);

        if (! $part->track_batch) {
            $this->stockService->issueForSale($branchId, $locationId, $partId, $quantity, $referenceId, $referenceNo, $userId);

            return;
        }

        $remaining = $quantity;
        $batches = StockBatch::where('branch_id', $branchId)
            ->where('location_id', $locationId)
            ->where('part_id', $partId)
            ->where('quantity', '>', 0)
            ->orderBy('received_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $take = min((float) $batch->quantity, $remaining);
            $batch->decrement('quantity', $take);
            $remaining -= $take;

            $this->stockService->issueForSale(
                $branchId, $locationId, $partId, $take, $referenceId, $referenceNo, $userId
            );

            StockMovement::where('part_id', $partId)
                ->where('reference_id', $referenceId)
                ->latest('id')
                ->limit(1)
                ->update(['stock_batch_id' => $batch->id]);
        }

        if ($remaining > 0) {
            throw new \RuntimeException("Insufficient batch stock for part {$part->part_number}");
        }
    }

    public function batchesForPart(int $branchId, int $locationId, int $partId)
    {
        return StockBatch::where('branch_id', $branchId)
            ->where('location_id', $locationId)
            ->where('part_id', $partId)
            ->where('quantity', '>', 0)
            ->orderBy('received_date')
            ->get();
    }
}
