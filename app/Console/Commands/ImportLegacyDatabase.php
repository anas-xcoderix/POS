<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\Part;
use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLegacyDatabase extends Command
{
    protected $signature = 'iaapco:import-legacy
                            {--connection=legacy_sqlsrv : Database connection name}
                            {--dry-run : List tables only without importing}
                            {--masters : Import brands, parts, customers, vendors from legacy tables}';

    protected $description = 'Import data from legacy IAAPCO SQL Server database (InventoryHas)';

    /** @var array<string, string> Legacy table => Laravel model table */
    protected array $masterMappings = [
        'Brand' => 'brands',
        'Brands' => 'brands',
        'tblBrand' => 'brands',
        'Part' => 'parts',
        'Parts' => 'parts',
        'tblPart' => 'parts',
        'Customer' => 'customers',
        'Customers' => 'customers',
        'tblCustomer' => 'customers',
        'Vendor' => 'vendors',
        'Vendors' => 'vendors',
        'tblVendor' => 'vendors',
    ];

    public function handle(): int
    {
        $connection = $this->option('connection');

        if (! config("database.connections.{$connection}")) {
            $this->error("Connection [{$connection}] not configured. Add LEGACY_DB_* vars to .env.");

            return self::FAILURE;
        }

        try {
            $tables = collect(DB::connection($connection)->select(
                "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME"
            ))->pluck('TABLE_NAME');
        } catch (\Throwable $e) {
            $this->error('Cannot connect to legacy database: '.$e->getMessage());
            $this->line('');
            $this->line('Configure in .env:');
            $this->line('  LEGACY_DB_HOST=JS-SERVER');
            $this->line('  LEGACY_DB_DATABASE=InventoryHas');
            $this->line('  LEGACY_DB_USERNAME=sa');
            $this->line('  LEGACY_DB_PASSWORD=your_password');
            $this->line('');
            $this->line('Requires PHP sqlsrv extension. Run: php artisan iaapco:import-legacy --dry-run');

            return self::FAILURE;
        }

        $this->info('Found '.$tables->count().' tables on legacy database.');

        if ($this->option('dry-run')) {
            foreach ($tables as $name) {
                $mapped = $this->masterMappings[$name] ?? null;
                $this->line('  - '.$name.($mapped ? " → {$mapped}" : ''));
            }

            return self::SUCCESS;
        }

        if ($this->option('masters')) {
            return $this->importMasters($connection, $tables);
        }

        $this->warn('Use --dry-run to list tables, or --masters to import master data.');
        $this->line('Customize column mappings in app/Console/Commands/ImportLegacyDatabase.php');

        return self::SUCCESS;
    }

    protected function importMasters(string $connection, $tables): int
    {
        $legacy = DB::connection($connection);
        $imported = 0;

        foreach ($this->masterMappings as $legacyTable => $target) {
            if (! $tables->contains($legacyTable)) {
                continue;
            }

            $rows = $legacy->table($legacyTable)->limit(5000)->get();
            $this->info("Importing {$rows->count()} rows from {$legacyTable} → {$target}");

            foreach ($rows as $row) {
                $data = (array) $row;
                match ($target) {
                    'brands' => $this->importBrand($data),
                    'parts' => $this->importPart($data),
                    'customers' => $this->importCustomer($data),
                    'vendors' => $this->importVendor($data),
                    default => null,
                };
                $imported++;
            }
        }

        $this->info("Processed {$imported} legacy rows.");

        return self::SUCCESS;
    }

    protected function importBrand(array $row): void
    {
        $name = $row['Name'] ?? $row['BrandName'] ?? $row['name'] ?? null;
        if (! $name) {
            return;
        }

        Brand::firstOrCreate(
            ['code' => $row['Code'] ?? $row['code'] ?? strtoupper(substr($name, 0, 10))],
            ['name' => $name, 'is_active' => true]
        );
    }

    protected function importPart(array $row): void
    {
        $partNo = $row['PartNo'] ?? $row['PartNumber'] ?? $row['part_number'] ?? null;
        if (! $partNo) {
            return;
        }

        $brand = Brand::first();
        if (! $brand) {
            $brand = Brand::create(['code' => 'DEF', 'name' => 'Default', 'is_active' => true]);
        }

        Part::updateOrCreate(
            ['part_number' => $partNo],
            [
                'brand_id' => $brand->id,
                'description_en' => $row['Description'] ?? $row['Desc'] ?? $partNo,
                'oem_no' => $row['OEMNo'] ?? $row['oem_no'] ?? null,
                'barcode' => $row['Barcode'] ?? $row['barcode'] ?? null,
                'list_price' => (float) ($row['ListPrice'] ?? $row['list_price'] ?? 0),
                'cost_price' => (float) ($row['CostPrice'] ?? $row['cost_price'] ?? 0),
                'is_active' => true,
            ]
        );
    }

    protected function importCustomer(array $row): void
    {
        $name = $row['Name'] ?? $row['CustomerName'] ?? $row['name'] ?? null;
        if (! $name) {
            return;
        }

        Customer::updateOrCreate(
            ['code' => $row['Code'] ?? $row['code'] ?? 'C'.substr(md5($name), 0, 8)],
            [
                'name' => $name,
                'phone' => $row['Phone'] ?? $row['phone'] ?? null,
                'email' => $row['Email'] ?? $row['email'] ?? null,
                'is_active' => true,
            ]
        );
    }

    protected function importVendor(array $row): void
    {
        $name = $row['Name'] ?? $row['VendorName'] ?? $row['name'] ?? null;
        if (! $name) {
            return;
        }

        Vendor::updateOrCreate(
            ['code' => $row['Code'] ?? $row['code'] ?? 'V'.substr(md5($name), 0, 8)],
            [
                'name' => $name,
                'phone' => $row['Phone'] ?? $row['phone'] ?? null,
                'email' => $row['Email'] ?? $row['email'] ?? null,
                'is_active' => true,
            ]
        );
    }
}
