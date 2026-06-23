<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Currency;
use App\Models\FixedAssetCategory;
use App\Models\Location;
use App\Models\PosTerminal;
use Illuminate\Database\Seeder;

class DesktopParitySeeder extends Seeder
{
    public function run(): void
    {
        Currency::updateOrCreate(
            ['code' => 'SAR'],
            [
                'name' => 'Saudi Riyal',
                'name_ar' => 'ريال سعودي',
                'symbol' => 'ر.س',
                'exchange_rate' => 1,
                'is_base' => true,
                'is_active' => true,
            ]
        );

        Currency::updateOrCreate(
            ['code' => 'USD'],
            [
                'name' => 'US Dollar',
                'name_ar' => 'دولار أمريكي',
                'symbol' => '$',
                'exchange_rate' => 3.75,
                'is_base' => false,
                'is_active' => true,
            ]
        );

        foreach (Branch::all() as $branch) {
            $locations = Location::where('branch_id', $branch->id)->orderBy('id')->get();

            if ($locations->isNotEmpty()) {
                $first = $locations->first();
                if ($first->location_type !== 'warehouse') {
                    $first->update(['location_type' => 'warehouse']);
                }
            }

            $this->ensureLocationType($branch->id, 'showroom', 'SHOW', 'Showroom');
            $this->ensureLocationType($branch->id, 'workshop', 'WORK', 'Workshop');

            $defaultLocation = Location::where('branch_id', $branch->id)
                ->where('location_type', 'warehouse')
                ->first();

            PosTerminal::updateOrCreate(
                ['code' => 'POS-'.$branch->code],
                [
                    'name' => $branch->name.' POS',
                    'branch_id' => $branch->id,
                    'default_location_id' => $defaultLocation?->id,
                    'is_active' => true,
                ]
            );
        }

        FixedAssetCategory::updateOrCreate(
            ['code' => 'GEN'],
            [
                'name' => 'General',
                'name_ar' => 'عام',
                'default_life_months' => 60,
                'is_active' => true,
            ]
        );

        \App\Models\ShowroomColor::updateOrCreate(['code' => 'WHT'], ['name' => 'White', 'name_ar' => 'أبيض', 'is_active' => true]);
        \App\Models\ShowroomColor::updateOrCreate(['code' => 'BLK'], ['name' => 'Black', 'name_ar' => 'أسود', 'is_active' => true]);
        \App\Models\ShowroomColor::updateOrCreate(['code' => 'SLV'], ['name' => 'Silver', 'name_ar' => 'فضي', 'is_active' => true]);

        \App\Models\ShowroomVehicleModel::updateOrCreate(
            ['code' => 'SEDAN'],
            ['name' => 'Sedan Standard', 'name_ar' => 'سيدان قياسي', 'make' => 'Generic', 'model_year' => (int) date('Y'), 'is_active' => true]
        );
        \App\Models\ShowroomVehicleModel::updateOrCreate(
            ['code' => 'SUV'],
            ['name' => 'SUV Standard', 'name_ar' => 'دفع رباعي قياسي', 'make' => 'Generic', 'model_year' => (int) date('Y'), 'is_active' => true]
        );
    }

    private function ensureLocationType(
        int $branchId,
        string $type,
        string $code,
        string $name
    ): void {
        $exists = Location::where('branch_id', $branchId)
            ->where('location_type', $type)
            ->exists();

        if (! $exists) {
            Location::create([
                'branch_id' => $branchId,
                'code' => $code,
                'name' => $name,
                'location_type' => $type,
                'is_active' => true,
            ]);
        }
    }
}
