<?php

use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'reports'], function () {
    // sales report by customer
    //----------------------------------
    Route::get('/sales/customers/{hash}', [ReportController::class, 'customersSalesReport'])->name('get.sales.customers');


    // sales report by items
    //----------------------------------
    Route::get('/sales/bill-ty/{hash}', [ReportController::class, 'itemsSalesReport'])->name('get.sales.items');


    // report for expenses
    //----------------------------------
    Route::get('/expenses/{hash}', [ReportController::class, 'expensesReport'])->name('get.expenses.reports');

    // report for profit and loss
    //----------------------------------
    Route::get('/profit-loss/{hash}', [ReportController::class, 'profitLossReport'])->name('get.profit.loss');

    // report for customers
    //----------------------------------
    Route::get('/customers/{hash}', [ReportController::class, 'customersReport'])->name('get.customers');
    Route::get('/credits/{hash}', [ReportController::class, 'CreditsReport'])->name('get.credits');

    // report for banks
    //----------------------------------
    Route::get('/banks/{hash}', [ReportController::class, 'banksReport'])->name('get.banks');

    // report for invoice
    Route::get('/invoice/{id}', [ReportController::class, 'invoiceReport'])->name('get.invoice');

    // report for slip
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


