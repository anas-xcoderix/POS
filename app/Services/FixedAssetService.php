<?php

namespace App\Services;

use App\Models\FixedAsset;
use App\Models\FixedAssetCategory;
use App\Models\FixedAssetDepreciation;
use Illuminate\Support\Facades\DB;

class FixedAssetService
{
    public function __construct(
        private AccountingService $accounting,
        private AuditService $audit,
    ) {}

    public function register(array $data): FixedAsset
    {
        return DB::transaction(function () use ($data) {
            $category = FixedAssetCategory::findOrFail($data['category_id']);
            $life = $data['useful_life_months'] ?? $category->default_life_months;

            $asset = FixedAsset::create(array_merge($data, [
                'useful_life_months' => $life,
                'net_book_value' => $data['purchase_value'],
                'accumulated_depreciation' => 0,
                'status' => 'active',
            ]));

            $this->audit->log('asset.registered', $asset, null, $asset->toArray(), $asset->asset_code);

            return $asset;
        });
    }

    public function runMonthlyDepreciation(int $year, int $month, ?int $userId = null): int
    {
        $count = 0;
        $assets = FixedAsset::where('status', 'active')->get();

        foreach ($assets as $asset) {
            if (FixedAssetDepreciation::where('fixed_asset_id', $asset->id)
                ->where('dep_year', $year)->where('dep_month', $month)->exists()) {
                continue;
            }

            $monthly = $this->monthlyDepreciation($asset);
            if ($monthly <= 0) {
                continue;
            }

            DB::transaction(function () use ($asset, $year, $month, $monthly, $userId, &$count) {
                FixedAssetDepreciation::create([
                    'fixed_asset_id' => $asset->id,
                    'dep_year' => $year,
                    'dep_month' => $month,
                    'amount' => $monthly,
                    'posted_by' => $userId,
                    'posted_at' => now(),
                ]);

                $asset->increment('accumulated_depreciation', $monthly);
                $asset->decrement('net_book_value', $monthly);

                if ($asset->fresh()->net_book_value <= $asset->salvage_value) {
                    $asset->update(['status' => 'fully_depreciated']);
                }

                $this->audit->log('asset.depreciation', $asset, null, [
                    'year' => $year, 'month' => $month, 'amount' => $monthly,
                ], $asset->asset_code);

                $count++;
            });
        }

        return $count;
    }

    public function monthlyDepreciation(FixedAsset $asset): float
    {
        $depreciable = max(0, (float) $asset->purchase_value - (float) $asset->salvage_value);
        $months = max(1, (int) $asset->useful_life_months);

        return round($depreciable / $months, 2);
    }

    public function dispose(FixedAsset $asset, ?string $remarks = null): FixedAsset
    {
        $asset->update([
            'status' => 'disposed',
            'disposed_at' => now()->toDateString(),
            'remarks' => trim(($asset->remarks ?? '').' '.$remarks),
        ]);

        $this->audit->log('asset.disposed', $asset, null, ['status' => 'disposed'], $asset->asset_code);

        return $asset->fresh();
    }
}
