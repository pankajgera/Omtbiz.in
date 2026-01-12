<?php

use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// sales report by customer
//----------------------------------
Route::get('/sales/customers/{hash}', [
    'as' => 'get.sales.customers',
    'uses' => 'ReportController@customersSalesReport'
]);


// sales report by items
//----------------------------------
Route::get('/sales/bill-ty/{hash}', [
    'as' => 'get.sales.items',
    'uses' => 'ReportController@itemsSalesReport'
]);


// report for expenses
//----------------------------------
Route::get('/expenses/{hash}', [
    'as' => 'get.expenses.reports',
    'uses' => 'ReportController@expensesReport'
]);

// report for profit and loss
//----------------------------------
Route::get('/profit-loss/{hash}', [
    'as' => 'get.profit.loss',
    'uses' => 'ReportController@profitLossReport'
]);

// report for customers
//----------------------------------
Route::get('/customers/{hash}', [
    'as' => 'get.customers',
    'uses' => 'ReportController@customersReport'
]);
Route::get('/credits/{hash}', [
    'as' => 'get.customers',
    'uses' => 'ReportController@CreditsReport'
]);

// report for banks
//----------------------------------
Route::get('/banks/{hash}', [
    'as' => 'get.banks',
    'uses' => 'ReportController@banksReport'
]);

// report for invoice
Route::get('/invoice/{id}', [
    'as' => 'get.invoice',
    'uses' => 'ReportController@invoiceReport'
]);

// report for slip
Route::get('/slip/{id}', [
    'as' => 'get.slip',
    'uses' => 'ReportController@slipReport'
]);

Route::get('/invoices/pdf/{id}', [FrontendController::class, 'getInvoicePdf'])->name('get.invoice.pdf');
Route::get('/receipts/pdf/{id}', [FrontendController::class, 'getReceiptPdf'])->name('get.receipt.pdf');
Route::get('/estimates/pdf/{id}', [FrontendController::class, 'getEstimatePdf'])->name('get.estimate.pdf');
Route::get('/customer/invoices/pdf/{id}', [FrontendController::class, 'getCustomerInvoicePdf'])->name('get.customer.invoice.pdf');
Route::get('/customer/estimates/pdf/{id}', [FrontendController::class, 'getCustomerEstimatePdf'])->name('get.customer.estimate.pdf');
Route::get('/expenses/{id}/receipt/{hash}', [ExpensesController::class, 'downloadReceipt'])->name('download.expense.receipt');

Route::get('/{vue?}', function () {
    return view('app');
})->where('vue', '[\/\w\.-]*')->name('base')->middleware('install');





