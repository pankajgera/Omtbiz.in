<?php

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
|
*/
Route::group(['prefix' => 'reports'], function () {

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
});

// download pdf with a unique_hash $id
// -------------------------------------------------
Route::get('/invoices/pdf/{id}', [
    'as' => 'get.invoice.pdf',
    'uses' => 'FrontendController@getInvoicePdf'
]);

Route::get('/receipts/pdf/{id}', [
    'as' => 'get.receipt.pdf',
    'uses' => 'FrontendController@getReceiptPdf'
]);

Route::get('/estimates/pdf/{id}', [
    'as' => 'get.estimate.pdf',
    'uses' => 'FrontendController@getEstimatePdf'
]);

Route::get('/customer/invoices/pdf/{id}', [
    'as' => 'get.customer.invoice.pdf',
    'uses' => 'FrontendController@getCustomerInvoicePdf'
]);

Route::get('/customer/estimates/pdf/{id}', [
    'as' => 'get.customer.estimate.pdf',
    'uses' => 'FrontendController@getCustomerEstimatePdf'
]);

Route::get('/expenses/{id}/receipt/{hash}', [
    'as' => 'download.expense.receipt',
    'uses' => 'ExpensesController@downloadReceipt'
]);


// Move other http requests to the Vue App
// -------------------------------------------------
Route::get('/{vue?}', function () {
    return view('app');
})->where('vue', '[\/\w\.-]*')->name('home')->middleware('install');
