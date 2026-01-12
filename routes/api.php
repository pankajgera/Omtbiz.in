<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Illuminate\Support\Facades\Route;

// Authentication & Password Reset
//----------------------------------
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [App\Http\Controllers\Auth\AccessTokensController::class, 'store']);
    Route::post('refresh_token', [App\Http\Controllers\Auth\AccessTokensController::class, 'update']);
    // Send reset password mail
    Route::post('password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail']);
    // handle reset password form process
    Route::post('reset/password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset']);
    Route::get('logout', [App\Http\Controllers\Auth\AccessTokensController::class, 'destroy']);
});

Route::post('is-registered', [App\Http\Controllers\Auth\AccessTokensController::class, 'isRegistered'])->name('is-registered');

Route::get('/ping', [App\Http\Controllers\UsersController::class, 'ping'])->name('ping');

Route::get('/logout', [App\Http\Controllers\Auth\AccessTokensController::class, 'destroy'])->name('logout');

// Country, State & City
//----------------------------------
Route::get('/countries', [App\Http\Controllers\LocationController::class, 'getCountries'])->name('countries');

// App version
// ----------------------------------
Route::get('/settings/app/version', [App\Http\Controllers\SettingsController::class, 'getAppVersion'])->name('settings.app.version');

Route::group(['middleware' => 'api'], function () {
    Route::get('/bootstrap', [App\Http\Controllers\UsersController::class, 'getBootstrap'])->name('bootstrap');

    // Customers
    //----------------------------------
    Route::post('/customers/delete', [App\Http\Controllers\CustomersController::class, 'delete'])->name('customers.delete');
    Route::resource('customers', App\Http\Controllers\CustomersController::class);

    // Items
    //----------------------------------
    Route::post('/bill-ty/delete', [App\Http\Controllers\ItemsController::class, 'delete'])->name('items.delete');
    Route::get('/bill-ty/dispatch', [App\Http\Controllers\ItemsController::class, 'getDispatch'])->name('items.dispatch');
    Route::resource('bill-ty', App\Http\Controllers\ItemsController::class);

    // Invoices
    //-------------------------------------------------
    Route::post('/invoices/create-invoice', [App\Http\Controllers\InvoicesController::class, 'store'])->name('invoices.create-invoice');
    Route::post('/invoices/delete', [App\Http\Controllers\InvoicesController::class, 'delete'])->name('invoices.delete');
    Route::get('/invoices/estimate/{estimate}', [App\Http\Controllers\InvoicesController::class, 'getInvoiceEstimate'])->name('estimate');
    Route::post('/invoices/reference', [App\Http\Controllers\InvoicesController::class, 'referenceNumber'])->name('invoices.reference');
    Route::get('/invoices/bulk', [App\Http\Controllers\InvoicesController::class, 'bulk'])->name('invoices.bulk');
    Route::resource('invoices', App\Http\Controllers\InvoicesController::class);

    // Estimates
    //-------------------------------------------------
    Route::post('/estimates/delete', [App\Http\Controllers\EstimatesController::class, 'delete'])->name('estimates.delete');
    Route::post('/estimates/send', [App\Http\Controllers\EstimatesController::class, 'sendEstimate'])->name('estimates.send');
    Route::post('/estimates/create-invoice', [App\Http\Controllers\EstimatesController::class, 'store'])->name('estimates.create-invoice');
    Route::resource('estimates', App\Http\Controllers\EstimatesController::class);

    // Orders
    //-------------------------------------------------
    Route::post('/orders/delete', [App\Http\Controllers\OrdersController::class, 'delete'])->name('orders.delete');
    Route::post('/orders/create-invoice', [App\Http\Controllers\OrdersController::class, 'store'])->name('orders.create-invoice');
    Route::resource('orders', App\Http\Controllers\OrdersController::class);

    // Expenses
    //----------------------------------
    Route::post('/expenses/delete', [App\Http\Controllers\ExpensesController::class, 'delete'])->name('expenses.delete');
    Route::get('/expenses/{id}/show/receipt', [App\Http\Controllers\ExpensesController::class, 'showReceipt']);
    Route::post('/expenses/{id}/upload/receipts', [App\Http\Controllers\ExpensesController::class, 'uploadReceipts'])->name('estimate.to.receipts');
    Route::resource('expenses', App\Http\Controllers\ExpensesController::class);

    // Expenses Categories
    //----------------------------------
    Route::resource('categories', App\Http\Controllers\ExpenseCategoryController::class);

    // Payments
    //----------------------------------
    Route::post('/payments/delete', [App\Http\Controllers\PaymentController::class, 'delete'])->name('payments.delete');
    Route::resource('payments', App\Http\Controllers\PaymentController::class);

    // Receipts
    //----------------------------------
    Route::post('/receipts/delete', [App\Http\Controllers\ReceiptController::class, 'delete'])->name('receipts.delete');
    Route::resource('receipts', App\Http\Controllers\ReceiptController::class);

    // Settings
    //----------------------------------
    Route::group(['prefix' => 'settings'], function () {
        Route::get('/notifications', [App\Http\Controllers\CompanyController::class, 'getNotifications'])->name('get.admin.notifications');
        Route::get('/profile', [App\Http\Controllers\CompanyController::class, 'getAdmin'])->name('get.admin.profile');

        // Erase Data
        //----------------------------------
        Route::delete('/data/delete', [App\Http\Controllers\CompanyController::class, 'delete'])->name('admin.data.delete');
        Route::put('/profile', [App\Http\Controllers\CompanyController::class, 'updateAdminProfile'])->name('admin.put.profile');
        Route::post('/profile/upload-avatar', [App\Http\Controllers\CompanyController::class, 'uploadAdminAvatar'])->name('admin.profile.avatar');
        Route::post('/company/upload-logo', [App\Http\Controllers\CompanyController::class, 'uploadCompanyLogo'])->name('upload.admin.company.logo');
        Route::get('/company', [App\Http\Controllers\CompanyController::class, 'getAdminCompany'])->name('get.admin.company');
        Route::post('/company', [App\Http\Controllers\CompanyController::class, 'updateAdminCompany'])->name('admin.company');
        Route::get('/general', [App\Http\Controllers\CompanyController::class, 'getGeneralSettings'])->name('get.admin.company.setting');
        Route::put('/general', [App\Http\Controllers\CompanyController::class, 'updateGeneralSettings'])->name('admin.company.setting');
        Route::get('/colors', [App\Http\Controllers\CompanyController::class, 'getColors'])->name('admin.colors.setting');
        Route::get('/get-setting', [App\Http\Controllers\CompanyController::class, 'getSetting'])->name('get.admin.setting');
        Route::get('/get-inventory-type', [App\Http\Controllers\CompanyController::class, 'getInventoryType'])->name('get.inventory.type');
        Route::put('/update-setting', [App\Http\Controllers\CompanyController::class, 'updateSetting'])->name('admin.update.setting');
        Route::get('/get-customize-setting', [App\Http\Controllers\CompanyController::class, 'getCustomizeSetting'])->name('admin.get.customize.setting');
        Route::put('/update-customize-setting', [App\Http\Controllers\CompanyController::class, 'updateCustomizeSetting'])->name('admin.update.customize.setting');
        Route::get('/environment/mail', [App\Http\Controllers\EnvironmentController::class, 'getMailDrivers'])->name('admin.environment.mail');
        Route::get('/environment/mail-env', [App\Http\Controllers\EnvironmentController::class, 'getMailEnvironment'])->name('admin.mail.env');
        Route::post('/environment/mail', [App\Http\Controllers\EnvironmentController::class, 'saveMailEnvironment'])->name('admin.environment.mail.save');
    });

    // Users Routes
    //----------------------------------
    Route::post('/users/delete', [App\Http\Controllers\UsersController::class, 'delete'])->name('users.delete');
    Route::get('/users/fetch-roles-and-companies', [App\Http\Controllers\UsersController::class, 'getRolesAndCompanies']);
    Route::resource('users', App\Http\Controllers\UsersController::class);

    // Notes
    //----------------------------------
    Route::post('/notes/delete', [App\Http\Controllers\NoteController::class, 'delete'])->name('notes.delete');
    Route::resource('notes', App\Http\Controllers\NoteController::class);

    // Inventory
    //----------------------------------
    Route::post('/inventory/delete', [App\Http\Controllers\InventoryController::class, 'delete'])->name('inventory.delete');
    Route::put('/inventory/increase-price', [App\Http\Controllers\InventoryController::class, 'increasePrice'])->name('inventory.price');
    Route::get('/inventory/stock', [App\Http\Controllers\InventoryController::class, 'getInventoryStock'])->name('inventory.stock');
    Route::get('/inventory/{id}/stock', [App\Http\Controllers\InventoryController::class, 'getInvoiceStock'])->name('invoice.stock');
    Route::resource('inventory', App\Http\Controllers\InventoryController::class);

    // Account Masters
    //----------------------------------
    Route::post('/masters/delete', [App\Http\Controllers\AccountMastersController::class, 'delete'])->name('masters.delete');
    Route::post('/master/check-name', [App\Http\Controllers\AccountMastersController::class, 'checkName']);
    Route::resource('masters', App\Http\Controllers\AccountMastersController::class);




    // Account Groups
    //----------------------------------
    Route::post('/groups/delete', [App\Http\Controllers\AccountGroupsController::class, 'delete'])->name('groups.delete');
    Route::resource('groups', App\Http\Controllers\AccountGroupsController::class);

    // Account Ledgers
    //----------------------------------
    Route::post('/ledgers/delete', [App\Http\Controllers\AccountLedgersController::class, 'delete'])->name('ledgers.delete');
    Route::get('/ledgers/{id}/display', [App\Http\Controllers\AccountLedgersController::class, 'display'])->name('ledgers.display');
    Route::get('/ledgers/{id}/daysheet', [App\Http\Controllers\AccountLedgersController::class, 'daysheet'])->name('ledgers.daysheet');
    Route::resource('ledgers', App\Http\Controllers\AccountLedgersController::class);

    // Vouchers
    //----------------------------------
    Route::post('/vouchers/delete', [App\Http\Controllers\VouchersController::class, 'delete'])->name('vouchers.delete');
    Route::post('/vouchers/update', [App\Http\Controllers\VouchersController::class, 'update']);
    Route::get('/vouchers/{id}/book', [App\Http\Controllers\VouchersController::class, 'book'])->name('vouchers.book');
    Route::get('/vouchers/daybook', [App\Http\Controllers\VouchersController::class, 'getDaybook'])->name('vouchers.daybook');
    Route::resource('vouchers', App\Http\Controllers\VouchersController::class);

    Route::post('/vouchers/update', 'VouchersController@update');

    Route::get('/vouchers/{id}/book', [
        'as' => 'vouchers.book',
        'uses' => 'VouchersController@book'
    ]);

    Route::get('/vouchers/daybook', [
        'as' => 'vouchers.daybook',
        'uses' => 'VouchersController@getDaybook'
    ]);

    Route::resource('vouchers', 'VouchersController');

    //States
    Route::resource('states', 'StatesController');


    //Get ledgers for report
    Route::get('reports/ledgers', 'ReportController@getLedgersInReport');
    Route::get('reports/ledger', 'ReportController@getCreditsLedgersInReport');

    // Get ledgers for report
    Route::get('reports/ledgers', [App\Http\Controllers\ReportController::class, 'getLedgersInReport']);

    // Banks
    //----------------------------------
    Route::post('/banks/delete', [App\Http\Controllers\BanksController::class, 'delete'])->name('banks.delete');
    Route::post('/banks/update', [App\Http\Controllers\BanksController::class, 'update']);
    Route::resource('banks', App\Http\Controllers\BanksController::class);

    // Dispatch
    //----------------------------------
    Route::post('/dispatch/delete', [App\Http\Controllers\DispatchController::class, 'delete'])->name('dispatch.delete');
    Route::post('/dispatch/multiple', [App\Http\Controllers\DispatchController::class, 'multiple'])->name('dispatch.multiple');
    Route::post('/dispatch/{id}/update', [App\Http\Controllers\DispatchController::class, 'updateDispatch']);
    Route::post('/dispatch/update-to-be', [App\Http\Controllers\DispatchController::class, 'updateToBeDispatch'])->name('dispatch.updatetobe');
    Route::post('/dispatch/to-be-edit', [App\Http\Controllers\DispatchController::class, 'tobeEdit'])->name('dispatch.tobeedit');
    Route::get('/dispatch/invoices', [App\Http\Controllers\DispatchController::class, 'getInvoices'])->name('dispatch');
    Route::resource('dispatch', App\Http\Controllers\DispatchController::class);

    Route::post('/dispatch/multiple', [
        'as' => 'dispatch.multiple',
        'uses' => 'DispatchController@multiple'
    ]);
    Route::post('/dispatch/{id}/update', 'DispatchController@updateDispatch');

    Route::post('/dispatch/update-to-be', [
        'as' => 'dispatch.updatetobe',
        'uses' => 'DispatchController@updateToBeDispatch'
    ]);

    Route::post('/dispatch/to-be-edit', [
        'as' => 'dispatch.tobeedit',
        'uses' => 'DispatchController@tobeEdit'
    ]);

    Route::get('/dispatch/invoices', [
        'as' => 'dispatch',
        'uses' => 'DispatchController@getInvoices'
    ]);

    Route::resource('dispatch', 'DispatchController');


    Route::post('/whatsapp-send-pdf', [
        'as' => 'whatsapp',
        'uses' => 'WhatsappController@sendPdf'
    ]);

    // Credits
    Route::get('/credits/{id}/credit', [
        'as' => 'masters.credit',
        'uses' => 'CreditsController@getHistory'
    ]);
});
