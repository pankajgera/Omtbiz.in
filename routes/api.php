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
    Route::get('logout', 'Auth\AccessTokensController@destroy');
    Route::post('refresh_token', 'Auth\AccessTokensController@update');

    // Send reset password mail
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');

    // handle reset password form process
    Route::post('reset/password', 'Auth\ResetPasswordController@reset');
});

Route::post('is-registered', [
    'as' => 'is-registered', 'uses' => 'Auth\AccessTokensController@isRegistered'
]);

Route::get('/ping', [
    'as' => 'ping',
    'uses' => 'UsersController@ping'
]);

// Country, State & City
//----------------------------------

Route::get('/countries', [
    'as' => 'countries',
    'uses' => 'LocationController@getCountries'
]);


// Onboarding
//----------------------------------
Route::group(['middleware' => 'redirect-if-installed'], function () {

    Route::get('/onboarding', [
        'as' => 'admin.onboarding',
        'uses' => 'OnboardingController@getOnboardingData'
    ]);

    Route::get('/onboarding/requirements', [
        'as' => 'admin.onboarding.requirements',
        'uses' => 'RequirementsController@requirements'
    ]);

    Route::get('/onboarding/permissions', [
        'as' => 'admin.onboarding.permissions',
        'uses' => 'PermissionsController@permissions'
    ]);

    Route::post('/onboarding/environment/database', [
        'as' => 'admin.onboarding.database',
        'uses' => 'EnvironmentController@saveDatabaseEnvironment'
    ]);

    Route::get('/onboarding/environment/mail', [
        'as' => 'admin.onboarding.mail',
        'uses' => 'EnvironmentController@getMailDrivers'
    ]);

    Route::post('/onboarding/environment/mail', [
        'as' => 'admin.onboarding.mail',
        'uses' => 'EnvironmentController@saveMailEnvironment'
    ]);

    Route::post('/onboarding/profile', [
        'as' => 'admin.profile',
        'uses' => 'OnboardingController@adminProfile'
    ]);

    Route::post('/profile/upload-avatar', [
        'as' => 'admin.on_boarding.avatar',
        'uses' => 'OnboardingController@uploadAdminAvatar'
    ]);

    Route::post('/onboarding/company', [
        'as' => 'admin.company',
        'uses' => 'OnboardingController@adminCompany'
    ]);

    Route::post('/onboarding/company/upload-logo', [
        'as' => 'upload.admin.company.logo',
        'uses' => 'CompanyController@uploadCompanyLogo'
    ]);

    Route::post('/onboarding/settings', [
        'as' => 'admin.settings',
        'uses' => 'OnboardingController@companySettings'
    ]);
});


// App version
// ----------------------------------

Route::get('/settings/app/version', [
    'as' => 'settings.app.version',
    'uses' => 'SettingsController@getAppVersion'
]);

Route::group(['middleware' => 'api'], function () {

    // Auto update routes
    //----------------------------------
    // Route::post('/update', [
    //     'as' => 'auto.update',
    //     'uses' => 'UpdateController@update'
    // ]);

    // Route::post('/update/finish', [
    //     'as' => 'auto.update.finish',
    //     'uses' => 'UpdateController@finishUpdate'
    // ]);

    // Route::get('/check/update', [
    //     'as' => 'check.update',
    //     'uses' => 'UpdateController@checkLatestVersion'
    // ]);

    Route::get('/bootstrap', [
        'as' => 'bootstrap',
        'uses' => 'UsersController@getBootstrap'
    ]);

    // Route::get('/bootstrap/accountant', [
    //     'as' => 'bootstrap',
    //     'uses' => 'UsersController@getBootstrapAccountant'
    // ])->middleware(['accountant']);

    // Route::get('/bootstrap/employee', [
    //     'as' => 'bootstrap',
    //     'uses' => 'UsersController@getBootstrapEmployee'
    // ])->middleware(['employee']);

    // Dashboard
    //----------------------------------

    // Route::get('/dashboard', [
    //     'as' => 'dashboard',
    //     'uses' => 'InvoicesController@index'
    // ])->middleware('admin');


    // Customers
    //----------------------------------

    Route::post('/customers/delete', [
        'as' => 'customers.delete',
        'uses' => 'CustomersController@delete'
    ]);

    Route::resource('customers', 'CustomersController');


    // Items
    //----------------------------------

    Route::post('/items/delete', [
        'as' => 'items.delete',
        'uses' => 'ItemsController@delete'
    ]);

    Route::resource('items', 'ItemsController');


    // Invoices
    //-------------------------------------------------

    Route::post('/invoices/delete', [
        'as' => 'invoices.delete',
        'uses' => 'InvoicesController@delete'
    ]);

    Route::post('/invoices/send', [
        'as' => 'invoices.send',
        'uses' => 'InvoicesController@sendInvoice'
    ]);

    Route::post('/invoices/mark-as-paid', [
        'as' => 'invoices.paid',
        'uses' => 'InvoicesController@markAsPaid'
    ]);

    Route::post('/invoices/mark-as-sent', [
        'as' => 'invoices.sent',
        'uses' => 'InvoicesController@markAsSent'
    ]);

    Route::get('/invoices/unpaid', [
        'as' => 'bootstrap',
        'uses' => 'InvoicesController@getCustomersUnpaidInvoices'
    ]);

    Route::post('/invoices/reference', [
        'as' => 'invoices.reference',
        'uses' => 'InvoicesController@referenceNumber'
    ]);

    Route::resource('invoices', 'InvoicesController');


    // Tax Types
    //----------------------------------

    Route::resource('tax-types', 'TaxTypeController');


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

    Route::post('/estimates/mark-as-sent', [
        'as' => 'estimates.send',
        'uses' => 'EstimatesController@markEstimateSent'
    ]);

    Route::post('/estimates/accept', [
        'as' => 'estimates.mark.accepted',
        'uses' => 'EstimatesController@markEstimateAccepted'
    ]);

    Route::post('/estimates/reject', [
        'as' => 'estimates.mark.rejected',
        'uses' => 'EstimatesController@markEstimateRejected'
    ]);

    Route::post('/estimates/{id}/convert-to-invoice', [
        'as' => 'estimate.to.invoice',
        'uses' => 'EstimatesController@estimateToInvoice'
    ]);

    Route::resource('estimates', 'EstimatesController');


    // Expenses
    //----------------------------------

    Route::post('/expenses/delete', [
        'as' => 'expenses.delete',
        'uses' => 'ExpensesController@delete'
    ]);

    Route::get('/expenses/{id}/show/receipt', [
        'as' => 'expenses.show',
        'uses' => 'ExpensesController@showReceipt',
    ]);

    Route::post('/expenses/{id}/upload/receipts', [
        'as' => 'estimate.to.invoice',
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

        Route::get('/profile', [
            'as' => 'get.admin.profile',
            'uses' => 'CompanyController@getAdmin'
        ]);

        Route::put('/profile', [
            'as' => 'admin.profile',
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

    Route::resource('inventory', 'InventoryController');


    // Account Masters
    //----------------------------------
    Route::post('/masters/delete', [
        'as' => 'masters.delete',
        'uses' => 'AccountMastersController@delete'
    ]);

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

    Route::resource('ledgers', 'AccountLedgersController');

    // Vouchers
    //----------------------------------
    Route::post('/vouchers/delete', [
        'as' => 'vouchers.delete',
        'uses' => 'VouchersController@delete'
    ]);

    Route::post('/vouchers/update', [
        'as' => 'vouchers.update',
        'uses' => 'VouchersController@update'
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

    Route::post('/banks/update', [
        'as' => 'banks.update',
        'uses' => 'BanksController@update'
    ]);

    Route::resource('banks', 'BanksController');


    // Dispatch
    //----------------------------------
    Route::post('/dispatch/delete', [
        'as' => 'dispatch.delete',
        'uses' => 'DispatchController@delete'
    ]);

    Route::post('/dispatch/update', [
        'as' => 'dispatch.update',
        'uses' => 'DispatchController@update'
    ]);

    Route::get('/dispatch/invoices', [
        'as' => 'bootstrap',
        'uses' => 'DispatchController@getInvoices'
    ]);

    Route::resource('dispatch', 'DispatchController');
});
