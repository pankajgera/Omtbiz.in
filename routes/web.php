<?php

use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('reports')->group(function () {
    Route::get('/sales/customers/{hash}', [ReportController::class, 'customersSalesReport'])->name('get.sales.customers');
    Route::get('/sales/bill-ty/{hash}', [ReportController::class, 'itemsSalesReport'])->name('get.sales.items');
    Route::get('/expenses/{hash}', [ReportController::class, 'expensesReport'])->name('get.expenses.reports');
    Route::get('/profit-loss/{hash}', [ReportController::class, 'profitLossReport'])->name('get.profit.loss');
    Route::get('/customers/{hash}', [ReportController::class, 'customersReport'])->name('get.customers');
    Route::get('/banks/{hash}', [ReportController::class, 'banksReport'])->name('get.banks');
    Route::get('/invoice/{id}', [ReportController::class, 'invoiceReport'])->name('get.invoice');
    Route::get('/slip/{id}', [ReportController::class, 'slipReport'])->name('get.slip');
});

Route::get('/invoices/pdf/{id}', [FrontendController::class, 'getInvoicePdf'])->name('get.invoice.pdf');
Route::get('/receipts/pdf/{id}', [FrontendController::class, 'getReceiptPdf'])->name('get.receipt.pdf');
Route::get('/estimates/pdf/{id}', [FrontendController::class, 'getEstimatePdf'])->name('get.estimate.pdf');
Route::get('/customer/invoices/pdf/{id}', [FrontendController::class, 'getCustomerInvoicePdf'])->name('get.customer.invoice.pdf');
Route::get('/customer/estimates/pdf/{id}', [FrontendController::class, 'getCustomerEstimatePdf'])->name('get.customer.estimate.pdf');
Route::get('/expenses/{id}/receipt/{hash}', [ExpensesController::class, 'downloadReceipt'])->name('download.expense.receipt');

Route::get('/{vue?}', function () {
    return view('app');
})->where('vue', '[\/\w\.-]*')->name('home')->middleware('install');
