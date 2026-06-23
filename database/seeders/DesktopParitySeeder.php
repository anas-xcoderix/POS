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

        \App\Models\LeaveType::updateOrCreate(['code' => 'ANNUAL'], ['name' => 'Annual Leave', 'name_ar' => 'إجازة سنوية', 'max_days_per_year' => 30, 'is_paid' => true, 'is_active' => true]);
        \App\Models\LeaveType::updateOrCreate(['code' => 'SICK'], ['name' => 'Sick Leave', 'name_ar' => 'إجازة مرضية', 'max_days_per_year' => 15, 'is_paid' => true, 'is_active' => true]);
        \App\Models\LeaveType::updateOrCreate(['code' => 'UNPAID'], ['name' => 'Unpaid Leave', 'name_ar' => 'إجازة بدون راتب', 'max_days_per_year' => 90, 'is_paid' => false, 'is_active' => true]);

        $year = (int) date('Y');
        foreach ([
            ['name' => 'Saudi National Day', 'name_ar' => 'اليوم الوطني', 'date' => "{$year}-09-23"],
            ['name' => 'Founding Day', 'name_ar' => 'يوم التأسيس', 'date' => "{$year}-02-22"],
            ['name' => 'Eid Al-Fitr', 'name_ar' => 'عيد الفطر', 'date' => "{$year}-04-10"],
            ['name' => 'Eid Al-Adha', 'name_ar' => 'عيد الأضحى', 'date' => "{$year}-06-16"],
        ] as $holiday) {
            \App\Models\PublicHoliday::updateOrCreate(
                ['holiday_date' => $holiday['date'], 'branch_id' => null],
                ['name' => $holiday['name'], 'name_ar' => $holiday['name_ar'], 'is_active' => true]
            );
        }

        $branch = Branch::first();
        if ($branch) {
            \App\Models\Employee::updateOrCreate(
                ['employee_no' => 'EMP-001'],
                [
                    'branch_id' => $branch->id,
                    'name' => 'Sample Employee',
                    'name_ar' => 'موظف تجريبي',
                    'job_title' => 'Sales Staff',
                    'basic_salary' => 5000,
                    'housing_allowance' => 1500,
                    'transport_allowance' => 500,
                    'gosi_eligible' => true,
                    'gosi_number' => 'GOSI-001',
                    'bank_name' => 'Al Rajhi Bank',
                    'bank_account' => 'SA1234567890',
                    'overtime_rate' => 25,
                    'is_active' => true,
                ]
            );
        }

        foreach (Branch::all() as $branch) {
            \App\Models\TransportDriver::updateOrCreate(
                ['code' => 'DRV-'.$branch->code],
                [
                    'name' => $branch->name.' Driver',
                    'name_ar' => 'سائق '.$branch->name_ar,
                    'phone' => '0500000000',
                    'license_no' => 'L-'.$branch->code,
                    'vehicle_plate' => 'ABC-'.$branch->code,
                    'branch_id' => $branch->id,
                    'is_active' => true,
                ]
            );
        }
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
