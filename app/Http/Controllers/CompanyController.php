<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Setting;
use App\Models\Company;
use App\Models\Address;
use App\Http\Requests\SettingRequest;
use App\Http\Requests\SettingKeyRequest;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\CompanyRequest;
use App\Http\Requests\CompanySettingRequest;
use App\Http\Requests\NotificationSettingsRequest;
use App\Space\CurrencyFormatter;
use App\Space\DateFormatter;
use App\Space\TimeZones;
use App\Models\Currency;
use App\Models\CompanySetting;
use Carbon\Carbon;
use App\Jobs\EraseData;
use Auth;
use Notification;

class CompanyController extends Controller
{
    /**
     * Retrive the Admin account.
     * @return \App\Models\User
     */
    public function getAdmin()
    {
        return User::find(1);
    }



    /**
     * Retrive the Admin account.
     * @return \App\Models\User
     */
    public function getNotifications()
    {
        return auth()->user()->notifications()
        ->whereNull('read_at')
        ->orderBy('id', 'desc')
        ->limit(10)
        ->get();

    }

    /**
     * Update the Admin profile.
     * Includes name, email and (or) password
     *
     * @param  \App\Http\Requests\ProfileRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAdminProfile(ProfileRequest $request)
    {
        $verifyEmail = User::where('email', $request->email)->first();

        $user = auth()->user();

        if ($verifyEmail) {
            if ($verifyEmail->id !== $user->id) {
                return response()->json([
                    'error' => 'Email already in use'
                ]);
            }
        }

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return response()->json([
            'user' => $user,
            'success' => true
        ]);
    }



    /**
     * Get Admin Account alongside the country from the addresses table and
     * The company from companies table
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdminCompany()
    {
        $user = User::with(['addresses', 'addresses.country', 'company'])->find(1);

        return response()->json([
            'user' => $user
        ]);
    }



    /**
     * Update Admin Company Details
     * @param \App\Http\Requests\CompanyRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAdminCompany(CompanyRequest $request)
    {
        $user = $request->user('api');
        $company = $user->company;
        $company->name = $request->name;
        $company->save();

        $user->update([
            'phone' => $request->phone,
        ]);

        if ($request->has('logo')) {
            $company->clearMediaCollection('logo');
            $company->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        $fields = $request->only(['address_street_1', 'address_street_2', 'city', 'state', 'country_id', 'zip', 'phone']);
        $address = Address::updateOrCreate(['user_id' => $user->id], $fields);
        $user = User::with(['addresses', 'addresses.country', 'company'])->find(1);

        return response()->json([
            'user' => $user,
            'success' => true
        ]);
    }

    /**
     * Retrieve General App Settings
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGeneralSettings(Request $request)
    {
        $date_formats = DateFormatter::get_list();

        $time_zones = TimeZones::get_list();
        $fiscal_years = [
            ['key' => 'january-december', 'value' => '1-12'],
            ['key' => 'february-january', 'value' => '2-1'],
            ['key' => 'march-february', 'value' => '3-2'],
            ['key' => 'april-march', 'value' => '4-3'],
            ['key' => 'may-april', 'value' => '5-4'],
            ['key' => 'june-may', 'value' => '6-5'],
            ['key' => 'july-june', 'value' => '7-6'],
            ['key' => 'august-july', 'value' => '8-7'],
            ['key' => 'september-august', 'value' => '9-8'],
            ['key' => 'october-september', 'value' => '10-9'],
            ['key' => 'november-october', 'value' => '11-10'],
            ['key' => 'december-november', 'value' => '12-11'],
        ];

        $language = CompanySetting::getSetting('language', $request->header('company'));
        $carbon_date_format = CompanySetting::getSetting('carbon_date_format', $request->header('company'));
        $moment_date_format = CompanySetting::getSetting('moment_date_format', $request->header('company'));
        $time_zone = CompanySetting::getSetting('time_zone', $request->header('company'));
        $currency = CompanySetting::getSetting('currency', $request->header('company'));
        $fiscal_year = CompanySetting::getSetting('fiscal_year', $request->header('company'));

        $languages = [
            ["code" => "en", "name" => "English"],
            ["code" => "fr", "name" => "French"],
            ["code" => "es", "name" => "Spanish"],
            ["code" => "ar", "name" => "العربية"],
        ];

        return response()->json([
            'languages' => $languages,
            'date_formats' => $date_formats,
            'time_zones' => $time_zones,
            'time_zone' => $time_zone,
            'currencies' => Currency::all(),
            'fiscal_years' => $fiscal_years,
            'fiscal_year' => $fiscal_year,
            'selectedLanguage' => $language,
            'selectedCurrency' => $currency,
            'carbon_date_format' => $carbon_date_format,
            'moment_date_format' => $moment_date_format,
        ]);
    }



    /**
     * Update General App Settings
     * @param \App\Http\Requests\CompanySettingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateGeneralSettings(CompanySettingRequest $request)
    {
        $sets = [
            'currency',
            'time_zone',
            'language',
            'carbon_date_format',
            'fiscal_year',
            'moment_date_format'
        ];

        foreach ($sets as $key) {
            CompanySetting::setSetting($key, $request->$key, $request->header('company'));
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function getCustomizeSetting(Request $request)
    {
        $invoice_prefix = CompanySetting::getSetting('invoice_prefix', $request->header('company'));
        $invoice_auto_generate = CompanySetting::getSetting('invoice_auto_generate', $request->header('company'));

        $reference_prefix = CompanySetting::getSetting('reference_prefix', $request->header('company'));
        $reference_auto_generate = CompanySetting::getSetting('reference_auto_generate', $request->header('company'));

        $estimate_prefix = CompanySetting::getSetting('estimate_prefix', $request->header('company'));
        $estimate_auto_generate  = CompanySetting::getSetting('estimate_auto_generate', $request->header('company'));

        $payment_prefix = CompanySetting::getSetting('payment_prefix', $request->header('company'));
        $payment_auto_generate = CompanySetting::getSetting('payment_auto_generate', $request->header('company'));

        return  response()->json([
            'invoice_prefix' => $invoice_prefix,
            'invoice_auto_generate' => $invoice_auto_generate,
            'reference_prefix' => $reference_prefix,
            'reference_auto_generate' => $reference_auto_generate,
            'estimate_prefix' => $estimate_prefix,
            'estimate_auto_generate' => $estimate_auto_generate,
            'payment_prefix' => $payment_prefix,
            'payment_auto_generate' => $payment_auto_generate,
        ]);
    }

    public function updateCustomizeSetting(Request $request)
    {
        $sets = [];

        if ($request->type == "PAYMENTS") {
            $sets = [
                'payment_prefix'
            ];
        }

        if ($request->type == "INVOICES") {
            $sets = [
                'invoice_prefix',
            ];
        }

        if ($request->type == "REFERENCES") {
            $sets = [
                'reference_prefix',
            ];
        }

        if ($request->type == "ESTIMATES") {
            $sets = [
                'estimate_prefix',
            ];
        }
        foreach ($sets as $key) {
            CompanySetting::setSetting($key, $request->$key, $request->header('company'));
        }

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Update a specific Company Setting
     * @param \App\Http\Requests\SettingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSetting(SettingRequest $request)
    {
        CompanySetting::setSetting($request->key, $request->value, $request->header('company'));

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Retrieve Specific Company Setting
     * @param \App\Http\Requests\SettingKeyRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSetting(SettingKeyRequest $request)
    {
        $setting = CompanySetting::getSetting($request->key, $request->header('company'));

        return response()->json([
            $request->key => $setting
        ]);
    }

    /**
     * Retrieve Inventory Type Specific Company Setting
     * @param \App\Http\Requests\SettingKeyRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInventoryType(SettingKeyRequest $request)
    {
        $setting = CompanySetting::getInventoryType($request->key, $request->header('company'));

        return response()->json([
            $request->key => $setting
        ]);
    }


    /**
     * Retrieve App Colors
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getColors(Request $request)
    {
        $colors = [
            'invoice_primary_color',
            'invoice_column_heading',
            'invoice_field_label',
            'invoice_field_value',
            'invoice_body_text',
            'invoice_description_text',
            'invoice_border_color',
            'primary_text_color',
            'heading_text_color',
            'section_heading_text_color',
            'border_color',
            'body_text_color',
            'footer_text_color',
            'footer_total_color',
            'footer_bg_color',
            'date_text_color'
        ];

        $colorSettings = CompanySetting::whereIn('option', $colors)
            ->whereCompany($request->header('company'))
            ->get();

        return response()->json([
            'colorSettings' => $colorSettings
        ]);
    }

    /**
     * Upload the company logo to storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadCompanyLogo(Request $request)
    {
        $data = json_decode($request->company_logo);

        if ($data) {
            $company = Company::find($request->header('company'));

            if ($company) {
                $company->clearMediaCollection('logo');

                $company->addMediaFromBase64($data->data)
                    ->usingFileName($data->name)
                    ->toMediaCollection('logo');
            }
        }

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Upload the Admin Avatar to public storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAdminAvatar(Request $request)
    {
        $data = json_decode($request->admin_avatar);

        if ($data) {
            $user = auth()->user();

            if ($user) {
                $user->clearMediaCollection('admin_avatar');

                $user->addMediaFromBase64($data->data)
                    ->usingFileName($data->name)
                    ->toMediaCollection('admin_avatar');
            }
        }

        return response()->json([
            'user' => $user,
            'success' => true
        ]);
    }

     /**
     * Retrive all Data .
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $job = new EraseData();
        dispatch($job);

        return response()->json([
            'success' => true
        ]);
    }
}
