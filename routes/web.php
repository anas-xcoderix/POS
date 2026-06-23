<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\FinanceReportController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\FranchiseController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OriginController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\PartImportController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\HrReportController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\JobCardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WorkshopReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware(['auth', 'verified', 'erp.permission'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('branches', BranchController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('brands', BrandController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('origins', OriginController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('franchises', FranchiseController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('locations', LocationController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('parts/import', [PartImportController::class, 'form'])->name('parts.import');
    Route::post('parts/import', [PartImportController::class, 'store'])->name('parts.import.store');
    Route::resource('parts', PartController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('customers', CustomerController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('vendors', VendorController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/movements', [StockController::class, 'movements'])->name('stock.movements');
    Route::get('/stock/adjustment', [StockController::class, 'adjustmentForm'])->name('stock.adjustment');
    Route::post('/stock/adjustment', [StockController::class, 'storeAdjustment'])->name('stock.adjustment.store');

    Route::resource('quotations', QuotationController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convert'])->name('quotations.convert');

    Route::resource('sales-invoices', SalesInvoiceController::class)->only(['index', 'create', 'store']);
    Route::post('sales-invoices/{sales_invoice}/post', [SalesInvoiceController::class, 'post'])->name('sales-invoices.post');

    Route::resource('sale-returns', SaleReturnController::class)->only(['index', 'create', 'store']);
    Route::post('sale-returns/{sale_return}/post', [SaleReturnController::class, 'post'])->name('sale-returns.post');

    Route::resource('stock-transfers', StockTransferController::class)->only(['index', 'create', 'store']);
    Route::post('stock-transfers/{stock_transfer}/complete', [StockTransferController::class, 'complete'])->name('stock-transfers.complete');

    Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');

    Route::resource('purchase-invoices', PurchaseInvoiceController::class)->only(['index', 'create', 'store']);
    Route::post('purchase-invoices/{purchase_invoice}/post', [PurchaseInvoiceController::class, 'post'])->name('purchase-invoices.post');

    Route::get('documents/sales-invoices/{sales_invoice}/pdf', [DocumentController::class, 'salesInvoicePdf'])->name('documents.sales-invoice.pdf');
    Route::get('documents/purchase-invoices/{purchase_invoice}/pdf', [DocumentController::class, 'purchaseInvoicePdf'])->name('documents.purchase-invoice.pdf');
    Route::get('documents/parts/{part}/label', [DocumentController::class, 'partBarcode'])->name('documents.part.label');
    Route::get('documents/parts/{part}/barcode.png', [DocumentController::class, 'partBarcodeImage'])->name('documents.part.barcode');

    Route::get('pricing/resolve', [PricingController::class, 'resolve'])->name('pricing.resolve');

    Route::resource('accounts', AccountController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('journal-entries', [JournalEntryController::class, 'index'])->name('journal-entries.index');
    Route::get('journal-entries/{journal_entry}', [JournalEntryController::class, 'show'])->name('journal-entries.show');

    Route::get('finance/reports', [FinanceReportController::class, 'index'])->name('finance.reports.index');
    Route::get('finance/reports/trial-balance', [FinanceReportController::class, 'trialBalance'])->name('finance.reports.trial-balance');
    Route::get('finance/reports/income-statement', [FinanceReportController::class, 'incomeStatement'])->name('finance.reports.income-statement');
    Route::get('finance/reports/balance-sheet', [FinanceReportController::class, 'balanceSheet'])->name('finance.reports.balance-sheet');
    Route::get('finance/reports/customer-aging', [FinanceReportController::class, 'customerAging'])->name('finance.reports.customer-aging');
    Route::get('finance/reports/vendor-aging', [FinanceReportController::class, 'vendorAging'])->name('finance.reports.vendor-aging');

    Route::resource('vehicles', VehicleController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('job-cards', JobCardController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('job-cards/{job_card}/status', [JobCardController::class, 'updateStatus'])->name('job-cards.update-status');
    Route::post('job-cards/{job_card}/convert', [JobCardController::class, 'convert'])->name('job-cards.convert');
    Route::get('workshop/reports/wip', [WorkshopReportController::class, 'wip'])->name('workshop.reports.wip');

    Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('employees', EmployeeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('payroll/create', [PayrollController::class, 'create'])->name('payroll.create');
    Route::post('payroll', [PayrollController::class, 'store'])->name('payroll.store');
    Route::get('payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::post('payroll/{payroll}/post', [PayrollController::class, 'post'])->name('payroll.post');
    Route::get('hr/reports/expiring-documents', [HrReportController::class, 'expiringDocuments'])->name('hr.reports.expiring-documents');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('reports/{report}/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('reports/{report}/csv', [ReportController::class, 'csv'])->name('reports.csv');

    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('discount-rules', [SettingsController::class, 'storeDiscountRule'])->name('discount-rules.store');
    Route::delete('discount-rules/{discountRule}', [SettingsController::class, 'destroyDiscountRule'])->name('discount-rules.destroy');

    Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
    Route::put('users/{user}', [UserManagementController::class, 'update'])->name('users.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
