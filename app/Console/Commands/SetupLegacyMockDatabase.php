<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SetupLegacyMockDatabase extends Command
{
    protected $signature = 'iaapco:legacy-mock-setup {--fresh : Recreate mock database file}';

    protected $description = 'Create a local SQLite mock of InventoryHas for testing iaapco:import-legacy';

    public function handle(): int
    {
        $path = database_path('legacy_mock.sqlite');

        if ($this->option('fresh') && File::exists($path)) {
            File::delete($path);
        }

        if (! File::exists($path)) {
            File::put($path, '');
        }

        config(['database.connections.legacy_sqlite.database' => $path]);
        $db = DB::connection('legacy_sqlite');

        $this->info('Creating mock legacy schema at: '.$path);

        $db->statement('PRAGMA foreign_keys = OFF');

        foreach ([
            'PayDetail', 'PayHeader', 'TransferDetail', 'TransferHeader', 'PODetail', 'POHeader',
            'RtnSalesDetail', 'RtnSalesHeader', 'PickDetail', 'PickHeader', 'ItemMove',
            'SalesDetail', 'SalesHeader', 'PurchaseDetail', 'PurchaseHeader', 'Employee',
            'ServiceVehicle', 'WIPStatus', 'Item', 'Customer', 'Vendor', 'Brand', 'Unit',
            'Department', 'Branch', 'Location', 'Account', 'GenDetail', 'GenHeader',
        ] as $t) {
            $db->statement("DROP TABLE IF EXISTS {$t}");
        }

        $db->statement('CREATE TABLE Branch (BranchID TEXT PRIMARY KEY, BranchCode TEXT, BranchName TEXT, Phone TEXT)');
        $db->statement('CREATE TABLE Department (DeptID TEXT PRIMARY KEY, DeptCode TEXT, DeptName TEXT)');
        $db->statement('CREATE TABLE Location (LocationID TEXT PRIMARY KEY, BranchID TEXT, LocationCode TEXT, LocationName TEXT)');
        $db->statement('CREATE TABLE Brand (BrandID TEXT PRIMARY KEY, BrandCode TEXT, BrandName TEXT)');
        $db->statement('CREATE TABLE Unit (UnitID TEXT PRIMARY KEY, UnitCode TEXT, UnitName TEXT)');
        $db->statement('CREATE TABLE Customer (CustCode TEXT PRIMARY KEY, CustName TEXT, Phone TEXT, CreditLimit REAL, Balance REAL, BranchID TEXT)');
        $db->statement('CREATE TABLE Vendor (VendCode TEXT PRIMARY KEY, VendName TEXT, Phone TEXT, Balance REAL)');
        $db->statement('CREATE TABLE Item (ItemCode TEXT PRIMARY KEY, Description TEXT, OEMNo TEXT, BrandID TEXT, SalePrice REAL, AveCost REAL, Qty1 REAL, Location1 TEXT, StoreID TEXT)');
        $db->statement('CREATE TABLE SalesHeader (SalesID TEXT PRIMARY KEY, SInvNo TEXT UNIQUE, BranchID TEXT, CustCode TEXT, InvDate TEXT, Posted INTEGER, TotalAmount REAL, InvType TEXT)');
        $db->statement('CREATE TABLE SalesDetail (ID INTEGER PRIMARY KEY AUTOINCREMENT, SalesID TEXT, ItemCode TEXT, Qty REAL, UnitPrice REAL, LineTotal REAL)');
        $db->statement('CREATE TABLE PickHeader (PickID TEXT PRIMARY KEY, PickNo TEXT UNIQUE, Qtype TEXT, BranchID TEXT, CustCode TEXT, PickDate TEXT, Posted INTEGER, TotalAmount REAL)');
        $db->statement('CREATE TABLE PickDetail (ID INTEGER PRIMARY KEY AUTOINCREMENT, PickID TEXT, ItemCode TEXT, Qty REAL, UnitPrice REAL, LineTotal REAL)');
        $db->statement('CREATE TABLE PurchaseHeader (PurchaseID TEXT PRIMARY KEY, InvNo TEXT UNIQUE, BranchID TEXT, VendCode TEXT, InvDate TEXT, Posted INTEGER, TotalAmount REAL)');
        $db->statement('CREATE TABLE PurchaseDetail (ID INTEGER PRIMARY KEY AUTOINCREMENT, PurchaseID TEXT, ItemCode TEXT, Qty REAL, UnitPrice REAL, LineTotal REAL)');
        $db->statement('CREATE TABLE POHeader (POID TEXT PRIMARY KEY, PONo TEXT UNIQUE, BranchID TEXT, VendCode TEXT, PODate TEXT, Posted INTEGER, TotalAmount REAL)');
        $db->statement('CREATE TABLE PODetail (ID INTEGER PRIMARY KEY AUTOINCREMENT, POID TEXT, ItemCode TEXT, Qty REAL, UnitPrice REAL, LineTotal REAL)');
        $db->statement('CREATE TABLE RtnSalesHeader (RtnID TEXT PRIMARY KEY, RtnInvNo TEXT UNIQUE, SInvNo TEXT, BranchID TEXT, CustCode TEXT, RtnDate TEXT, Posted INTEGER, TotalAmount REAL)');
        $db->statement('CREATE TABLE RtnSalesDetail (ID INTEGER PRIMARY KEY AUTOINCREMENT, RtnID TEXT, ItemCode TEXT, RtnQty REAL, UnitPrice REAL, LineTotal REAL)');
        $db->statement('CREATE TABLE Account (AccCode TEXT PRIMARY KEY, AcNameEng TEXT, AccType TEXT, Balance REAL)');
        $db->statement('CREATE TABLE GenHeader (GenID TEXT PRIMARY KEY, EntryNo TEXT UNIQUE, BranchID TEXT, EntryDate TEXT, Description TEXT, Posted INTEGER)');
        $db->statement('CREATE TABLE GenDetail (ID INTEGER PRIMARY KEY AUTOINCREMENT, GenID TEXT, AccCode TEXT, Debit REAL, Credit REAL)');
        $db->statement('CREATE TABLE PayHeader (PayID TEXT PRIMARY KEY, ReceiptNo TEXT UNIQUE, BranchID TEXT, CustCode TEXT, PayDate TEXT, PayMethod TEXT, Amount REAL, SInvNo TEXT, Posted INTEGER)');
        $db->statement('CREATE TABLE PayDetail (ID INTEGER PRIMARY KEY AUTOINCREMENT, PayID TEXT, AccCode TEXT, Amount REAL)');
        $db->statement('CREATE TABLE TransferHeader (TransferID TEXT PRIMARY KEY, TransferNo TEXT UNIQUE, FromBranchID TEXT, ToBranchID TEXT, TransferDate TEXT, Posted INTEGER)');
        $db->statement('CREATE TABLE TransferDetail (ID INTEGER PRIMARY KEY AUTOINCREMENT, TransferID TEXT, ItemCode TEXT, Qty REAL, UnitCost REAL)');
        $db->statement('CREATE TABLE ItemMove (MoveID TEXT PRIMARY KEY, BranchID TEXT, ItemCode TEXT, LocationID TEXT, MoveType TEXT, RefNo TEXT, QtyIn REAL, QtyOut REAL, MoveDate TEXT)');
        $db->statement('CREATE TABLE Employee (EmpID TEXT PRIMARY KEY, EmployeeNo TEXT, BranchID TEXT, DeptID TEXT, EmpName TEXT, Phone TEXT, HireDate TEXT, BasicSalary REAL)');
        $db->statement('CREATE TABLE ServiceVehicle (VehicleID TEXT PRIMARY KEY, CustCode TEXT, PlateNo TEXT, Make TEXT, Model TEXT, Year INTEGER, VIN TEXT)');
        $db->statement('CREATE TABLE WIPStatus (WIPID TEXT PRIMARY KEY, JobNo TEXT UNIQUE, BranchID TEXT, CustCode TEXT, VehicleID TEXT, JobDate TEXT, Status TEXT, TotalAmount REAL)');

        $db->table('Branch')->insert([
            ['BranchID' => '1', 'BranchCode' => 'HO', 'BranchName' => 'Head Office', 'Phone' => '0500000000'],
            ['BranchID' => '2', 'BranchCode' => 'BR2', 'BranchName' => 'Branch 2', 'Phone' => '0500000001'],
        ]);

        $db->table('Department')->insert([
            ['DeptID' => 'D1', 'DeptCode' => 'SALES', 'DeptName' => 'Sales'],
            ['DeptID' => 'D2', 'DeptCode' => 'WH', 'DeptName' => 'Warehouse'],
        ]);

        $db->table('Location')->insert([
            ['LocationID' => 'LOC1', 'BranchID' => '1', 'LocationCode' => 'MAIN', 'LocationName' => 'Main Warehouse'],
            ['LocationID' => 'LOC2', 'BranchID' => '2', 'LocationCode' => 'BR2-MAIN', 'LocationName' => 'Branch 2 Store'],
        ]);

        $db->table('Brand')->insert([
            ['BrandID' => 'B1', 'BrandCode' => 'TOY', 'BrandName' => 'Toyota OEM'],
        ]);

        $db->table('Unit')->insert([
            ['UnitID' => 'U1', 'UnitCode' => 'EA', 'UnitName' => 'Each'],
        ]);

        $db->table('Customer')->insert([
            ['CustCode' => 'C001', 'CustName' => 'Legacy Customer One', 'Phone' => '0501111111', 'CreditLimit' => 50000, 'Balance' => 1200, 'BranchID' => '1'],
            ['CustCode' => 'C002', 'CustName' => 'Legacy Customer Two', 'Phone' => '0502222222', 'CreditLimit' => 10000, 'Balance' => 0, 'BranchID' => '2'],
        ]);

        $db->table('Vendor')->insert([
            ['VendCode' => 'V001', 'VendName' => 'Legacy Vendor', 'Phone' => '0503333333', 'Balance' => 800],
        ]);

        $db->table('Item')->insert([
            ['ItemCode' => 'BRK-001', 'Description' => 'Brake Pad Set', 'OEMNo' => 'OEM-BRK-001', 'BrandID' => 'B1', 'SalePrice' => 150, 'AveCost' => 90, 'Qty1' => 25, 'Location1' => 'LOC1', 'StoreID' => '1'],
            ['ItemCode' => 'OIL-001', 'Description' => 'Engine Oil 5W30', 'OEMNo' => 'OEM-OIL', 'BrandID' => 'B1', 'SalePrice' => 45, 'AveCost' => 28, 'Qty1' => 100, 'Location1' => 'LOC1', 'StoreID' => '1'],
        ]);

        $db->table('PickHeader')->insert([
            ['PickID' => 'Q1', 'PickNo' => 'QUO-LEG-001', 'Qtype' => 'Q', 'BranchID' => '1', 'CustCode' => 'C001', 'PickDate' => '2026-01-12', 'Posted' => 0, 'TotalAmount' => 195],
        ]);
        $db->table('PickDetail')->insert([
            ['PickID' => 'Q1', 'ItemCode' => 'BRK-001', 'Qty' => 1, 'UnitPrice' => 150, 'LineTotal' => 150],
            ['PickID' => 'Q1', 'ItemCode' => 'OIL-001', 'Qty' => 1, 'UnitPrice' => 45, 'LineTotal' => 45],
        ]);

        $db->table('SalesHeader')->insert([
            ['SalesID' => 'S1', 'SInvNo' => 'INV-LEG-001', 'BranchID' => '1', 'CustCode' => 'C001', 'InvDate' => '2026-01-15', 'Posted' => 1, 'TotalAmount' => 345, 'InvType' => 'cash'],
        ]);

        $db->table('SalesDetail')->insert([
            ['SalesID' => 'S1', 'ItemCode' => 'BRK-001', 'Qty' => 2, 'UnitPrice' => 150, 'LineTotal' => 300],
            ['SalesID' => 'S1', 'ItemCode' => 'OIL-001', 'Qty' => 1, 'UnitPrice' => 45, 'LineTotal' => 45],
        ]);

        $db->table('PurchaseHeader')->insert([
            ['PurchaseID' => 'P1', 'InvNo' => 'PI-LEG-001', 'BranchID' => '1', 'VendCode' => 'V001', 'InvDate' => '2026-01-10', 'Posted' => 1, 'TotalAmount' => 560],
        ]);

        $db->table('PurchaseDetail')->insert([
            ['PurchaseID' => 'P1', 'ItemCode' => 'BRK-001', 'Qty' => 4, 'UnitPrice' => 90, 'LineTotal' => 360],
            ['PurchaseID' => 'P1', 'ItemCode' => 'OIL-001', 'Qty' => 10, 'UnitPrice' => 20, 'LineTotal' => 200],
        ]);

        $db->table('Account')->insert([
            ['AccCode' => '1000', 'AcNameEng' => 'Cash', 'AccType' => 'asset', 'Balance' => 10000],
            ['AccCode' => '4000', 'AcNameEng' => 'Sales Revenue', 'AccType' => 'revenue', 'Balance' => 0],
        ]);

        $db->table('GenHeader')->insert([
            ['GenID' => 'G1', 'EntryNo' => 'JE-LEG-001', 'BranchID' => '1', 'EntryDate' => '2026-01-31', 'Description' => 'Opening legacy entry', 'Posted' => 1],
        ]);

        $db->table('GenDetail')->insert([
            ['GenID' => 'G1', 'AccCode' => '1000', 'Debit' => 1000, 'Credit' => 0],
            ['GenID' => 'G1', 'AccCode' => '4000', 'Debit' => 0, 'Credit' => 1000],
        ]);

        $db->table('POHeader')->insert([
            ['POID' => 'PO1', 'PONo' => 'PO-LEG-001', 'BranchID' => '1', 'VendCode' => 'V001', 'PODate' => '2026-01-05', 'Posted' => 1, 'TotalAmount' => 560],
        ]);
        $db->table('PODetail')->insert([
            ['POID' => 'PO1', 'ItemCode' => 'BRK-001', 'Qty' => 4, 'UnitPrice' => 90, 'LineTotal' => 360],
            ['POID' => 'PO1', 'ItemCode' => 'OIL-001', 'Qty' => 10, 'UnitPrice' => 20, 'LineTotal' => 200],
        ]);

        $db->table('RtnSalesHeader')->insert([
            ['RtnID' => 'R1', 'RtnInvNo' => 'SR-LEG-001', 'SInvNo' => 'INV-LEG-001', 'BranchID' => '1', 'CustCode' => 'C001', 'RtnDate' => '2026-01-20', 'Posted' => 1, 'TotalAmount' => 45],
        ]);
        $db->table('RtnSalesDetail')->insert([
            ['RtnID' => 'R1', 'ItemCode' => 'OIL-001', 'RtnQty' => 1, 'UnitPrice' => 45, 'LineTotal' => 45],
        ]);

        $db->table('PayHeader')->insert([
            ['PayID' => 'PAY1', 'ReceiptNo' => 'RCPT-LEG-001', 'BranchID' => '1', 'CustCode' => 'C001', 'PayDate' => '2026-01-16', 'PayMethod' => 'cash', 'Amount' => 345, 'SInvNo' => 'INV-LEG-001', 'Posted' => 1],
        ]);

        $db->table('TransferHeader')->insert([
            ['TransferID' => 'T1', 'TransferNo' => 'TRF-LEG-001', 'FromBranchID' => '1', 'ToBranchID' => '2', 'TransferDate' => '2026-01-18', 'Posted' => 1],
        ]);
        $db->table('TransferDetail')->insert([
            ['TransferID' => 'T1', 'ItemCode' => 'OIL-001', 'Qty' => 5, 'UnitCost' => 28],
        ]);

        $db->table('ItemMove')->insert([
            ['MoveID' => 'M1', 'BranchID' => '1', 'ItemCode' => 'BRK-001', 'LocationID' => 'LOC1', 'MoveType' => 'RECEIPT', 'RefNo' => 'PI-LEG-001', 'QtyIn' => 4, 'QtyOut' => 0, 'MoveDate' => '2026-01-10'],
            ['MoveID' => 'M2', 'BranchID' => '1', 'ItemCode' => 'BRK-001', 'LocationID' => 'LOC1', 'MoveType' => 'SALE', 'RefNo' => 'INV-LEG-001', 'QtyIn' => 0, 'QtyOut' => 2, 'MoveDate' => '2026-01-15'],
        ]);

        $db->table('Employee')->insert([
            ['EmpID' => 'E1', 'EmployeeNo' => 'EMP-001', 'BranchID' => '1', 'DeptID' => 'D1', 'EmpName' => 'Legacy Employee', 'Phone' => '0504444444', 'HireDate' => '2024-01-01', 'BasicSalary' => 5000],
        ]);

        $db->table('ServiceVehicle')->insert([
            ['VehicleID' => 'V1', 'CustCode' => 'C001', 'PlateNo' => 'ABC-1234', 'Make' => 'Toyota', 'Model' => 'Camry', 'Year' => 2020, 'VIN' => 'VIN123456789'],
        ]);

        $db->table('WIPStatus')->insert([
            ['WIPID' => 'J1', 'JobNo' => 'JC-LEG-001', 'BranchID' => '1', 'CustCode' => 'C001', 'VehicleID' => 'V1', 'JobDate' => '2026-01-22', 'Status' => 'open', 'TotalAmount' => 850],
        ]);

        $this->info('Mock legacy database ready.');
        $this->line('Run: php artisan iaapco:import-legacy --connection=legacy_sqlite --phase=all --fresh-maps --force');

        return self::SUCCESS;
    }
}
