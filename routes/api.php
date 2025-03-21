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


// Authentication & Password Reset
//----------------------------------
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'Auth\AccessTokensController@store');
    Route::post('refresh_token', 'Auth\AccessTokensController@update');
    // Send reset password mail
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    // handle reset password form process
    Route::post('reset/password', 'Auth\ResetPasswordController@reset');
    Route::get('logout', 'Auth\AccessTokensController@destroy');
});

Route::post('is-registered', [
    'as' => 'is-registered', 'uses' => 'Auth\AccessTokensController@isRegistered'
]);

Route::get('/ping', [
    'as' => 'ping',
    'uses' => 'UsersController@ping'
]);

Route::get('/logout', [
    'as' => 'logout',
    'uses' => 'Auth\AccessTokensController@destroy'
]);

// Country, State & City
//----------------------------------

Route::get('/countries', [
    'as' => 'countries',
    'uses' => 'LocationController@getCountries'
]);

// App version
// ----------------------------------

Route::get('/settings/app/version', [
    'as' => 'settings.app.version',
    'uses' => 'SettingsController@getAppVersion'
]);

Route::group(['middleware' => 'api'], function () {
    Route::get('/bootstrap', [
        'as' => 'bootstrap',
        'uses' => 'UsersController@getBootstrap'
    ]);

    // Customers
    //----------------------------------

    Route::post('/customers/delete', [
        'as' => 'customers.delete',
        'uses' => 'CustomersController@delete'
    ]);

    Route::resource('customers', 'CustomersController');

    // Items
    //----------------------------------

    Route::post('/bill-ty/delete', [
        'as' => 'items.delete',
        'uses' => 'ItemsController@delete'
    ]);

    Route::get('/bill-ty/dispatch', [
        'as' => 'items.dispatch',
        'uses' => 'ItemsController@getDispatch'
    ]);

    Route::resource('bill-ty', 'ItemsController');


    // Invoices
    //-------------------------------------------------

    Route::post('/invoices/create-invoice', [
        'as' => 'invoices.create-invoice',
        'uses' => 'InvoicesController@store'
    ]);

    Route::post('/invoices/delete', [
        'as' => 'invoices.delete',
        'uses' => 'InvoicesController@delete'
    ]);

    Route::get('/invoices/estimate/{estimate}', [
        'as' => 'estimate',
        'uses' => 'InvoicesController@getInvoiceEstimate'
    ]);

    Route::post('/invoices/reference', [
        'as' => 'invoices.reference',
        'uses' => 'InvoicesController@referenceNumber'
    ]);

    Route::get('/invoices/bulk', [
        'as' => 'invoices.bulk',
        'uses' => 'InvoicesController@bulk'
    ]);

    Route::resource('invoices', 'InvoicesController');

    // Estimates
    //-------------------------------------------------

    Route::post('/estimates/delete', [
        'as' => 'estimates.delete',
        'uses' => 'EstimatesController@delete'
    ]);

    Route::post('/estimates/send', [
        'as' => 'estimates.send',
        'uses' => 'EstimatesController@sendEstimate'
    ]);

    Route::post('/estimates/create-invoice', [
        'as' => 'estimates.create-invoice',
        'uses' => 'EstimatesController@store'
    ]);

    Route::resource('estimates', 'EstimatesController');


    // Orders
    //-------------------------------------------------

    Route::post('/orders/delete', [
        'as' => 'orders.delete',
        'uses' => 'OrdersController@delete'
    ]);

    Route::post('/orders/create-invoice', [
        'as' => 'orders.create-invoice',
        'uses' => 'OrdersController@store'
    ]);

    Route::resource('orders', 'OrdersController');

    // Expenses
    //----------------------------------

    Route::post('/expenses/delete', [
        'as' => 'expenses.delete',
        'uses' => 'ExpensesController@delete'
    ]);

    Route::get('/expenses/{id}/show/receipt', 'ExpensesController@showReceipt');

    Route::post('/expenses/{id}/upload/receipts', [
        'as' => 'estimate.to.receipts',
        'uses' => 'ExpensesController@uploadReceipts'
    ]);

    Route::resource('expenses', 'ExpensesController');


    // Expenses Categories
    //----------------------------------

    Route::resource('categories', 'ExpenseCategoryController');


    // Payments
    //----------------------------------

    Route::post('/payments/delete', [
        'as' => 'payments.delete',
        'uses' => 'PaymentController@delete'
    ]);

    Route::resource('payments', 'PaymentController');

    // Receipts
    //----------------------------------

    Route::post('/receipts/delete', [
        'as' => 'receipts.delete',
        'uses' => 'ReceiptController@delete'
    ]);

    Route::resource('receipts', 'ReceiptController');


    // Settings
    //----------------------------------

    Route::group(['prefix' => 'settings'], function () {
        Route::get('/notifications', [
            'as' => 'get.admin.notifications',
            'uses' => 'CompanyController@getNotifications'
        ]);

        Route::get('/profile', [
            'as' => 'get.admin.profile',
            'uses' => 'CompanyController@getAdmin'
        ]);

        // Erase Data
        //----------------------------------

        Route::delete('/data/delete', [
            'as' => 'admin.data.delete',
            'uses' => 'CompanyController@delete'
        ]);

        Route::put('/profile', [
            'as' => 'admin.put.profile',
            'uses' => 'CompanyController@updateAdminProfile'
        ]);

        Route::post('/profile/upload-avatar', [
            'as' => 'admin.profile.avatar',
            'uses' => 'CompanyController@uploadAdminAvatar'
        ]);

        Route::post('/company/upload-logo', [
            'as' => 'upload.admin.company.logo',
            'uses' => 'CompanyController@uploadCompanyLogo'
        ]);

        Route::get('/company', [
            'as' => 'get.admin.company',
            'uses' => 'CompanyController@getAdminCompany'
        ]);

        Route::post('/company', [
            'as' => 'admin.company',
            'uses' => 'CompanyController@updateAdminCompany'
        ]);

        Route::get('/general', [
            'as' => 'get.admin.company.setting',
            'uses' => 'CompanyController@getGeneralSettings'
        ]);

        Route::put('/general', [
            'as' => 'admin.company.setting',
            'uses' => 'CompanyController@updateGeneralSettings'
        ]);

        Route::get('/colors', [
            'as' => 'admin.colors.setting',
            'uses' => 'CompanyController@getColors'
        ]);

        Route::get('/get-setting', [
            'as' => 'get.admin.setting',
            'uses' => 'CompanyController@getSetting'
        ]);

        Route::get('/get-inventory-type', [
            'as' => 'get.inventory.type',
            'uses' => 'CompanyController@getInventoryType'
        ]);

        Route::put('/update-setting', [
            'as' => 'admin.update.setting',
            'uses' => 'CompanyController@updateSetting'
        ]);

        Route::get('/get-customize-setting', [
            'as' => 'admin.get.customize.setting',
            'uses' => 'CompanyController@getCustomizeSetting'
        ]);

        Route::put('/update-customize-setting', [
            'as' => 'admin.update.customize.setting',
            'uses' => 'CompanyController@updateCustomizeSetting'
        ]);

        Route::get('/environment/mail', [
            'as' => 'admin.environment.mail',
            'uses' => 'EnvironmentController@getMailDrivers'
        ]);

        Route::get('/environment/mail-env', [
            'as' => 'admin.mail.env',
            'uses' => 'EnvironmentController@getMailEnvironment'
        ]);

        Route::post('/environment/mail', [
            'as' => 'admin.environment.mail.save',
            'uses' => 'EnvironmentController@saveMailEnvironment'
        ]);
    });

    // Users Routes
    //----------------------------------
    Route::post('/users/delete', [
        'as' => 'users.delete',
        'uses' => 'UsersController@delete'
    ]);

    Route::get('/users/fetch-roles-and-companies', 'UsersController@getRolesAndCompanies');

    Route::resource('users', 'UsersController');

    // Notes
    //----------------------------------
    Route::post('/notes/delete', [
        'as' => 'notes.delete',
        'uses' => 'NoteController@delete'
    ]);

    Route::resource('notes', 'NoteController');


    // Inventroy
    //----------------------------------
    Route::post('/inventory/delete', [
        'as' => 'inventory.delete',
        'uses' => 'InventoryController@delete'
    ]);

    Route::put('/inventory/increase-price', [
        'as' => 'inventory.price',
        'uses' => 'InventoryController@increasePrice'
    ]);

    Route::get('/inventory/stock', [
        'as' => 'inventory.stock',
        'uses' => 'InventoryController@getInventoryStock'
    ]);
    Route::get('/inventory/{id}/stock', [
        'as' => 'invoice.stock',
        'uses' => 'InventoryController@getInvoiceStock'
    ]);

    Route::resource('inventory', 'InventoryController');


    // Account Masters
    //----------------------------------
    Route::post('/masters/delete', [
        'as' => 'masters.delete',
        'uses' => 'AccountMastersController@delete'
    ]);

    Route::post('/master/check-name', 'AccountMastersController@checkName');

    Route::resource('masters', 'AccountMastersController');

    // Account Groups
    //----------------------------------
    Route::post('/groups/delete', [
        'as' => 'groups.delete',
        'uses' => 'AccountGroupsController@delete'
    ]);

    Route::resource('groups', 'AccountGroupsController');

    // Account Ledgers
    //----------------------------------
    Route::post('/ledgers/delete', [
        'as' => 'ledgers.delete',
        'uses' => 'AccountLedgersController@delete'
    ]);

    Route::get('/ledgers/{id}/display', [
        'as' => 'ledgers.display',
        'uses' => 'AccountLedgersController@display'
    ]);

    Route::get('/ledgers/{id}/daysheet', [
        'as' => 'ledgers.daysheet',
        'uses' => 'AccountLedgersController@daysheet'
    ]);

    Route::resource('ledgers', 'AccountLedgersController');

    // Vouchers
    //----------------------------------
    Route::post('/vouchers/delete', [
        'as' => 'vouchers.delete',
        'uses' => 'VouchersController@delete'
    ]);

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


    // Banks
    //----------------------------------
    Route::post('/banks/delete', [
        'as' => 'banks.delete',
        'uses' => 'BanksController@delete'
    ]);

    Route::post('/banks/update', 'BanksController@update');

    Route::resource('banks', 'BanksController');


    // Dispatch
    //----------------------------------
    Route::post('/dispatch/delete', [
        'as' => 'dispatch.delete',
        'uses' => 'DispatchController@delete'
    ]);

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
});
