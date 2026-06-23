<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Department;
use App\Models\DiscountRule;
use App\Models\Employee;
use App\Models\Franchise;
use App\Models\Location;
use App\Models\Origin;
use App\Models\Part;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Services\SettingService;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(SettingService::class)->seedDefaults();

        $this->call(ChartOfAccountsSeeder::class);

        $branch = Branch::create([
            'code' => 'HO',
            'name' => 'Head Office',
            'name_ar' => 'المكتب الرئيسي',
            'is_head_office' => true,
            'is_active' => true,
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'branch_id' => $branch->id,
            'role' => 'admin',
            'max_discount_percent' => 100,
            'can_access_all_branches' => true,
            'is_active' => true,
        ]);

        $brand = Brand::create(['code' => 'TOY', 'name' => 'Toyota', 'name_ar' => 'تويوتا']);
        $origin = Origin::create(['code' => 'JP', 'name' => 'Japan', 'name_ar' => 'اليابان']);
        $franchise = Franchise::create(['code' => 'GEN', 'name' => 'General', 'name_ar' => 'عام']);

        $location = Location::create([
            'branch_id' => $branch->id,
            'code' => 'A-01-01',
            'name' => 'Main Store A1',
            'aisle' => 'A',
            'rack' => '01',
            'bin' => '01',
        ]);

        Part::create([
            'part_number' => 'BRK-001',
            'oem_no' => '04465-02220',
            'brand_id' => $brand->id,
            'origin_id' => $origin->id,
            'franchise_id' => $franchise->id,
            'default_location_id' => $location->id,
            'description_en' => 'Brake Pad Set Front',
            'description_ar' => 'فحمات فرامل أمامية',
            'list_price' => 150.00,
            'price_2' => 140.00,
            'price_3' => 130.00,
            'cost_price' => 95.00,
        ]);

        Part::create([
            'part_number' => 'SVC-LABOR',
            'brand_id' => $brand->id,
            'origin_id' => $origin->id,
            'franchise_id' => $franchise->id,
            'description_en' => 'Workshop Labor / Service',
            'description_ar' => 'أجور ورشة',
            'list_price' => 0,
            'cost_price' => 0,
        ]);

        $workshopDept = Department::create(['code' => 'WS', 'name' => 'Workshop', 'name_ar' => 'الورشة']);
        Department::create(['code' => 'HR', 'name' => 'Human Resources', 'name_ar' => 'الموارد البشرية']);

        Employee::create([
            'employee_no' => 'EMP-001',
            'branch_id' => $branch->id,
            'department_id' => $workshopDept->id,
            'name' => 'Ahmed Mechanic',
            'job_title' => 'Senior Mechanic',
            'basic_salary' => 4500,
            'housing_allowance' => 500,
            'transport_allowance' => 300,
            'hire_date' => now()->subYear()->toDateString(),
            'aqama_no' => '2456789012',
            'aqama_expiry' => now()->addMonths(4)->toDateString(),
            'license_no' => 'DL-99887',
            'license_expiry' => now()->addMonths(8)->toDateString(),
        ]);

        $garageCustomer = Customer::create([
            'code' => 'CUST-001',
            'branch_id' => $branch->id,
            'name' => 'Walk-in Customer',
            'customer_type' => 'retail',
            'price_level' => 1,
        ]);

        $wholesaleCustomer = Customer::create([
            'code' => 'CUST-002',
            'branch_id' => $branch->id,
            'name' => 'Wholesale Garage',
            'customer_type' => 'wholesale',
            'price_level' => 2,
            'credit_limit' => 50000,
            'discount_percent' => 5,
        ]);

        Vehicle::create([
            'customer_id' => $wholesaleCustomer->id,
            'plate_no' => 'ABC-1234',
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => '2020',
            'color' => 'White',
            'istimara_expiry' => now()->addMonths(3)->toDateString(),
        ]);

        Vendor::create([
            'code' => 'VEND-001',
            'name' => 'Main Supplier',
            'phone' => '0500000000',
        ]);

        DiscountRule::create([
            'name' => 'Toyota Brand Discount',
            'rule_type' => 'brand',
            'brand_id' => $brand->id,
            'discount_percent' => 3,
            'priority' => 10,
            'is_active' => true,
        ]);
    }
}
