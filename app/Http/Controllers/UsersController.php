<?php
namespace Crater\Http\Controllers;

use Illuminate\Http\Request;
use Crater\Http\Requests;
use Crater\User;
use Crater\Currency;
use Crater\Setting;
use Crater\Item;
use Crater\TaxType;
use DB;
use Carbon\Carbon;
use Auth;
use Crater\Company;
use Crater\CompanySetting;
use Exception;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller
{
    public function getBootstrap(Request $request)
    {
        $user = Auth::user();

        $company = $request->header('company') ?? 1;

        $customers = User::with('billingAddress', 'shippingAddress')
            ->customer()
            ->whereCompany($company)
            ->latest()
            ->get();

        $currencies = Currency::latest()->get();

        $default_language = CompanySetting::getSetting('language', $company);

        $default_currency = Currency::findOrFail(
            CompanySetting::getSetting('currency', $company)
        );

        $moment_date_format = CompanySetting::getSetting(
            'moment_date_format',
            $request->header('company')
        );

        $fiscal_year = CompanySetting::getSetting(
            'fiscal_year',
            $request->header('company')
        );

        $items = Item::all();

        $taxTypes = TaxType::latest()->get();

        $companies = Company::all()->toArray();

        $roles = Role::all()->toArray();

        return response()->json([
            'user' => $user,
            'customers' => $customers,
            'currencies' => $currencies,
            'default_currency' => $default_currency,
            'default_language' => $default_language,
            'company' => $user->company,
            'companies' => $companies,
            'roles' => $roles,
            'items' => $items,
            'taxTypes' => $taxTypes,
            'moment_date_format' => $moment_date_format,
            'fiscal_year' => $fiscal_year,
        ]);
    }

    public function ping()
    {
        return response()->json([
            'success' => 'crater-self-hosted'
        ]);
    }

    public function getAddUser(Request $request)
    {
        $companies = Company::all()->toArray();

        $roles = Role::all()->toArray();

        return response()->json([
            'companies' => $companies,
            'roles' => $roles
        ]);
    }

    public function updateAddUser(Request $request)
    {
        try {
            $user = User::updateOrCreate(
                ['email' => $request->email, 'role' => $request->role['name'], 'company_id' => $request->company['id']],
                ['name' => $request->name, 'company_name' => $request->company['name'], 'password' => Hash::make($request->password)]
            );
            return response()->json([
                'user' => $user,
                'success' => true
            ]);
        } catch (Exception $e) {
            throw ValidationException::withMessages(['field' => ['Error while adding new user '. $e]]);
        }
    }
}
