<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FranchiseController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OriginController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('branches', BranchController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('brands', BrandController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('origins', OriginController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('franchises', FranchiseController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('locations', LocationController::class)->only(['index', 'store', 'update', 'destroy']);
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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
