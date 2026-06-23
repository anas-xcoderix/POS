<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Franchise;
use App\Models\Location;
use App\Models\Origin;
use App\Models\Part;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::create([
            'code' => 'HO',
            'name' => 'Head Office',
            'name_ar' => 'المكتب الرئيسي',
            'is_head_office' => true,
            'is_active' => true,
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@iaapco.local',
            'password' => Hash::make('password'),
            'branch_id' => $branch->id,
            'role' => 'admin',
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
            'cost_price' => 95.00,
        ]);

        Customer::create([
            'code' => 'CUST-001',
            'branch_id' => $branch->id,
            'name' => 'Walk-in Customer',
            'customer_type' => 'retail',
        ]);

        Vendor::create([
            'code' => 'VEND-001',
            'name' => 'Main Supplier',
            'phone' => '0500000000',
        ]);
    }
}
