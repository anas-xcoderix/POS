<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MasterPrintController;
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
use App\Http\Controllers\LegacyImportController;
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
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\HrReportController;
use App\Http\Controllers\PublicHolidayController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\JobCardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WorkshopReportController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\PaymentReceiptController;
use App\Http\Controllers\FiscalPeriodController;
use App\Http\Controllers\StockCountController;
use App\Http\Controllers\PartKitController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ChequeController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\VehicleOrderController;
use App\Http\Controllers\VehicleExpenseController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ProformaInvoiceController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PickTicketController;
use App\Http\Controllers\CashBookController;
use App\Http\Controllers\FixedAssetController;
use App\Http\Controllers\FixedAssetCategoryController;
use App\Http\Controllers\StockBatchController;
use App\Http\Controllers\TransportCashVoucherController;
use App\Http\Controllers\TransportDriverController;
use App\Http\Controllers\TransportShipmentController;
use App\Http\Controllers\TransportShippingStatusController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ShowroomVehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::post('locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch')->where('locale', 'en|ar');

Route::middleware(['auth', 'verified', 'erp.permission'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('branches', BranchController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('brands', BrandController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('origins', OriginController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('franchises', FranchiseController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('units', UnitController::class)->only(['index', 'store', 'update', 'destroy']);
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
    Route::get('sales-invoices/{sales_invoice}/edit-posted', [SalesInvoiceController::class, 'editPosted'])->name('sales-invoices.edit-posted');
    Route::put('sales-invoices/{sales_invoice}/edit-posted', [SalesInvoiceController::class, 'updatePosted'])->name('sales-invoices.update-posted');
    Route::post('sales-invoices/{sales_invoice}/post', [SalesInvoiceController::class, 'post'])->name('sales-invoices.post');
    Route::post('sales-invoices/{sales_invoice}/void', [SalesInvoiceController::class, 'void'])->name('sales-invoices.void');

    Route::resource('sale-returns', SaleReturnController::class)->only(['index', 'create', 'store']);
    Route::post('sale-returns/{sale_return}/post', [SaleReturnController::class, 'post'])->name('sale-returns.post');

    Route::resource('purchase-returns', PurchaseReturnController::class)->only(['index', 'create', 'store']);
    Route::post('purchase-returns/{purchase_return}/post', [PurchaseReturnController::class, 'post'])->name('purchase-returns.post');

    Route::resource('payments', PaymentReceiptController::class)->only(['index', 'create', 'store']);
    Route::get('customers/{customer}/statement', [PaymentReceiptController::class, 'customerStatement'])->name('customers.statement');
    Route::get('vendors/{vendor}/statement', [PaymentReceiptController::class, 'vendorStatement'])->name('vendors.statement');

    Route::resource('stock-transfers', StockTransferController::class)->only(['index', 'create', 'store']);
    Route::post('stock-transfers/{stock_transfer}/complete', [StockTransferController::class, 'complete'])->name('stock-transfers.complete');

    Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');

    Route::resource('purchase-invoices', PurchaseInvoiceController::class)->only(['index', 'create', 'store']);
    Route::get('purchase-invoices/{purchase_invoice}/edit-posted', [PurchaseInvoiceController::class, 'editPosted'])->name('purchase-invoices.edit-posted');
    Route::put('purchase-invoices/{purchase_invoice}/edit-posted', [PurchaseInvoiceController::class, 'updatePosted'])->name('purchase-invoices.update-posted');
    Route::post('purchase-invoices/{purchase_invoice}/post', [PurchaseInvoiceController::class, 'post'])->name('purchase-invoices.post');
    Route::post('purchase-invoices/{purchase_invoice}/void', [PurchaseInvoiceController::class, 'void'])->name('purchase-invoices.void');

    Route::get('stock-counts', [StockCountController::class, 'index'])->name('stock-counts.index');
    Route::get('stock-counts/create', [StockCountController::class, 'create'])->name('stock-counts.create');
    Route::post('stock-counts', [StockCountController::class, 'store'])->name('stock-counts.store');
    Route::get('stock-counts/{stock_count}', [StockCountController::class, 'show'])->name('stock-counts.show');
    Route::post('stock-counts/{stock_count}/post', [StockCountController::class, 'post'])->name('stock-counts.post');

    Route::get('parts/{part}/kits', [PartKitController::class, 'edit'])->name('parts.kits');
    Route::post('parts/{part}/kits', [PartKitController::class, 'storeKit'])->name('parts.kits.store');
    Route::delete('parts/{part}/kits/{kit}', [PartKitController::class, 'destroyKit'])->name('parts.kits.destroy');
    Route::post('parts/{part}/alternatives', [PartKitController::class, 'storeAlternative'])->name('parts.alternatives.store');
    Route::delete('parts/{part}/alternatives/{alternative}', [PartKitController::class, 'destroyAlternative'])->name('parts.alternatives.destroy');

    Route::get('documents/sales-invoices/{sales_invoice}/pdf', [DocumentController::class, 'salesInvoicePdf'])->name('documents.sales-invoice.pdf');
    Route::get('documents/purchase-invoices/{purchase_invoice}/pdf', [DocumentController::class, 'purchaseInvoicePdf'])->name('documents.purchase-invoice.pdf');
    Route::get('documents/delivery-notes/{delivery_note}/pdf', [DocumentController::class, 'deliveryNotePdf'])->name('documents.delivery-note.pdf');
    Route::get('documents/quotations/{quotation}/pdf', [DocumentController::class, 'quotationPdf'])->name('documents.quotation.pdf');
    Route::get('documents/purchase-orders/{purchase_order}/pdf', [DocumentController::class, 'purchaseOrderPdf'])->name('documents.purchase-order.pdf');
    Route::get('documents/payment-receipts/{payment_receipt}/pdf', [DocumentController::class, 'paymentReceiptPdf'])->name('documents.payment-receipt.pdf');
    Route::get('documents/payroll/{payroll_run}/items/{payroll_item}/pdf', [DocumentController::class, 'payslipPdf'])->name('documents.payslip.pdf');
    Route::get('documents/parts/{part}/label', [DocumentController::class, 'partBarcode'])->name('documents.part.label');
    Route::get('documents/parts/{part}/barcode.png', [DocumentController::class, 'partBarcodeImage'])->name('documents.part.barcode');
    Route::get('documents/masters/customers/pdf', [MasterPrintController::class, 'customers'])->name('documents.masters.customers.pdf');
    Route::get('documents/masters/vendors/pdf', [MasterPrintController::class, 'vendors'])->name('documents.masters.vendors.pdf');
    Route::get('documents/masters/parts/pdf', [MasterPrintController::class, 'parts'])->name('documents.masters.parts.pdf');

    Route::get('pricing/resolve', [PricingController::class, 'resolve'])->name('pricing.resolve');

    Route::resource('accounts', AccountController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('journal-entries', [JournalEntryController::class, 'index'])->name('journal-entries.index');
    Route::get('journal-entries/create', [JournalEntryController::class, 'create'])->name('journal-entries.create');
    Route::post('journal-entries', [JournalEntryController::class, 'store'])->name('journal-entries.store');
    Route::get('journal-entries/{journal_entry}', [JournalEntryController::class, 'show'])->name('journal-entries.show');

    Route::get('finance/periods', [FiscalPeriodController::class, 'index'])->name('finance.periods.index');
    Route::post('finance/periods/close', [FiscalPeriodController::class, 'close'])->name('finance.periods.close');
    Route::post('finance/periods/{fiscalPeriod}/reopen', [FiscalPeriodController::class, 'reopen'])->name('finance.periods.reopen');

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
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('payroll/create', [PayrollController::class, 'create'])->name('payroll.create');
    Route::post('payroll', [PayrollController::class, 'store'])->name('payroll.store');
    Route::get('payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::post('payroll/{payroll}/post', [PayrollController::class, 'post'])->name('payroll.post');
    Route::post('payroll/{payroll}/pay', [PayrollController::class, 'pay'])->name('payroll.pay');
    Route::patch('payroll/{payroll}/items/{item}', [PayrollController::class, 'updateItem'])->name('payroll.update-item');
    Route::post('payroll/{payroll}/regenerate', [PayrollController::class, 'regenerate'])->name('payroll.regenerate');
    Route::resource('public-holidays', PublicHolidayController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('leave', [LeaveRequestController::class, 'index'])->name('leave.index');
    Route::get('leave/create', [LeaveRequestController::class, 'create'])->name('leave.create');
    Route::post('leave', [LeaveRequestController::class, 'store'])->name('leave.store');
    Route::post('leave/{leave}/approve', [LeaveRequestController::class, 'approve'])->name('leave.approve');
    Route::post('leave/{leave}/reject', [LeaveRequestController::class, 'reject'])->name('leave.reject');
    Route::get('hr/reports/expiring-documents', [HrReportController::class, 'expiringDocuments'])->name('hr.reports.expiring-documents');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('reports/{report}/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('reports/{report}/csv', [ReportController::class, 'csv'])->name('reports.csv');

    Route::resource('cheques', ChequeController::class)->only(['index', 'create', 'store', 'update']);
    Route::resource('delivery-notes', DeliveryNoteController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('vehicle-orders', VehicleOrderController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('vehicles/{vehicle}/expenses', [VehicleExpenseController::class, 'index'])->name('vehicles.expenses');
    Route::post('vehicles/{vehicle}/expenses', [VehicleExpenseController::class, 'store'])->name('vehicles.expenses.store');
    Route::delete('vehicle-expenses/{vehicleExpense}', [VehicleExpenseController::class, 'destroy'])->name('vehicle-expenses.destroy');

    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('settings/legacy-import', [LegacyImportController::class, 'index'])->name('legacy-import.index');
    Route::post('settings/legacy-import/run', [LegacyImportController::class, 'run'])->name('legacy-import.run');
    Route::post('discount-rules', [SettingsController::class, 'storeDiscountRule'])->name('discount-rules.store');
    Route::delete('discount-rules/{discountRule}', [SettingsController::class, 'destroyDiscountRule'])->name('discount-rules.destroy');

    Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
    Route::put('users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::put('users/{user}/permissions', [UserManagementController::class, 'updatePermissions'])->name('users.permissions.update');

    Route::resource('currencies', CurrencyController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('currencies/{currency}/rate', [CurrencyController::class, 'setRate'])->name('currencies.set-rate');

    Route::resource('proforma-invoices', ProformaInvoiceController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('proforma-invoices/{proforma_invoice}/convert', [ProformaInvoiceController::class, 'convert'])->name('proforma-invoices.convert');

    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('pos/terminals/{terminal}/open-session', [PosController::class, 'openSession'])->name('pos.open-session');
    Route::get('pos/sessions/{session}', [PosController::class, 'counter'])->name('pos.counter');
    Route::get('pos/sessions/{session}/search-parts', [PosController::class, 'searchParts'])->name('pos.search-parts');
    Route::get('pos/sessions/{session}/report', [PosController::class, 'sessionReport'])->name('pos.session-report');
    Route::post('pos/sessions/{session}/sale', [PosController::class, 'quickSale'])->name('pos.quick-sale');
    Route::post('pos/sessions/{session}/close', [PosController::class, 'closeSession'])->name('pos.close-session');

    Route::get('pick-tickets', [PickTicketController::class, 'index'])->name('pick-tickets.index');
    Route::post('pick-tickets/from-invoice/{sales_invoice}', [PickTicketController::class, 'createFromInvoice'])->name('pick-tickets.create-from-invoice');
    Route::get('pick-tickets/{pick_ticket}', [PickTicketController::class, 'show'])->name('pick-tickets.show');
    Route::post('pick-tickets/{pick_ticket}/confirm', [PickTicketController::class, 'confirm'])->name('pick-tickets.confirm');

    Route::get('cash-book', [CashBookController::class, 'index'])->name('cash-book.index');
    Route::get('cash-book/create', [CashBookController::class, 'create'])->name('cash-book.create');
    Route::post('cash-book', [CashBookController::class, 'store'])->name('cash-book.store');

    Route::resource('fixed-assets', FixedAssetController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('fixed-assets/depreciate', [FixedAssetController::class, 'runDepreciation'])->name('fixed-assets.depreciate');
    Route::resource('fixed-asset-categories', FixedAssetCategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('stock-batches', [StockBatchController::class, 'index'])->name('stock-batches.index');

    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    Route::get('showroom-vehicles', [ShowroomVehicleController::class, 'index'])->name('showroom-vehicles.index');
    Route::get('showroom-vehicles/create', [ShowroomVehicleController::class, 'create'])->name('showroom-vehicles.create');
    Route::post('showroom-vehicles', [ShowroomVehicleController::class, 'store'])->name('showroom-vehicles.store');
    Route::get('showroom-vehicles/{showroom_vehicle}', [ShowroomVehicleController::class, 'show'])->name('showroom-vehicles.show');
    Route::post('showroom-vehicles/{showroom_vehicle}/transfer', [ShowroomVehicleController::class, 'transfer'])->name('showroom-vehicles.transfer');
    Route::post('showroom-vehicles/{showroom_vehicle}/sell', [ShowroomVehicleController::class, 'sell'])->name('showroom-vehicles.sell');
    Route::post('showroom-transfers/{showroomVehicleTransfer}/receive', [ShowroomVehicleController::class, 'receiveTransfer'])->name('showroom-transfers.receive');

    Route::resource('transport-drivers', TransportDriverController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::prefix('transport')->name('transport.')->group(function () {
        Route::get('shipments', [TransportShipmentController::class, 'index'])->name('shipments.index');
        Route::get('shipments/create', [TransportShipmentController::class, 'create'])->name('shipments.create');
        Route::post('shipments', [TransportShipmentController::class, 'store'])->name('shipments.store');
        Route::get('shipments/{shipment}', [TransportShipmentController::class, 'show'])->name('shipments.show');
        Route::patch('shipments/{shipment}/status', [TransportShipmentController::class, 'updateStatus'])->name('shipments.update-status');
        Route::post('shipments/from-delivery-note/{deliveryNote}', [TransportShipmentController::class, 'createFromDeliveryNote'])->name('shipments.from-delivery-note');

        Route::get('cash-vouchers', [TransportCashVoucherController::class, 'index'])->name('cash-vouchers.index');
        Route::get('cash-vouchers/create', [TransportCashVoucherController::class, 'create'])->name('cash-vouchers.create');
        Route::post('cash-vouchers', [TransportCashVoucherController::class, 'store'])->name('cash-vouchers.store');
        Route::get('cash-vouchers/{cashVoucher}', [TransportCashVoucherController::class, 'show'])->name('cash-vouchers.show');
        Route::post('cash-vouchers/{cashVoucher}/post', [TransportCashVoucherController::class, 'post'])->name('cash-vouchers.post');

        Route::get('shipping-status', [TransportShippingStatusController::class, 'index'])->name('shipping-status.index');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
