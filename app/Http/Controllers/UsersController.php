<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use App\Models\Currency;
use App\Models\Setting;
use App\Models\Item;
use App\Models\TaxType;
use DB;
use Carbon\Carbon;
use Auth;
use App\Models\Company;
use App\Models\CompanySetting;
use Exception;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\UserRequest;

class UsersController extends Controller
{
    /**
     * Load all required data on login to state
     */
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

    /**
     * Ping test
     */
    public function ping()
    {
        return response()->json([
            'success' => 'crater-self-hosted'
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $limit = $request->has('limit') ? $request->limit : 10;

            $users = User::addedUsers()
                ->applyFilters($request->only([
                    'display_name',
                    'email',
                    'role'
                ]))
                ->whereCompany($request->header('company'))
                ->select(
                    'users.*',
                    DB::raw('sum(invoices.due_amount) as due_amount')
                )
                ->groupBy('users.id')
                ->leftJoin('invoices', 'users.id', '=', 'invoices.user_id')
                ->paginate($limit);

            $siteData = [
                'users' => $users
            ];

            return response()->json($siteData);

        } catch (Exception $e) {
            return ['error_message' => $e->getMessage()];
        }
    }

    /***
     * Get all roles and companies
     */
    public function getRolesAndCompanies() {
        $companies = Company::all()->toArray();
        $roles = Role::all()->toArray();

        return response()->json([
            'companies' => $companies,
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserRequest $request)
    {
        try {
            if (null !== $request->email) {
                $verifyEmail = User::where('email', $request->email)->first();

                if ($verifyEmail) {
                    return response()->json([
                        'error' => 'Email already in use'
                    ]);
                }
            }

            $user = new User();
            $user->name = $request->name;
            $user->company_id = $request->header('company');
            $user->email = $request->email;
            $user->company_name = $request->company;
            $user->contact_name = $request->contact_name;
            $user->website = $request->website;
            $user->role = $request->role;
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'user' => $user,
                'success' => true
            ]);
        } catch (Exception $e) {
            return ['error_message' => $e->getMessage()];
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = User::find($id);

            return response()->json([
                'user' => $user
            ]);
        } catch (Exception $e) {
            return ['error_message' => $e->getMessage()];
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
            $companies = Company::all()->toArray();
            $roles = Role::all()->toArray();

            return response()->json([
                'user' => $user,
                'companies' => $companies,
                'roles' => $roles,
            ]);
        } catch (Exception $e) {
            return ['error_message' => $e->getMessage()];
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, UserRequest $request)
    {
        try {
            $user = User::find($id);

            if ($request->email != null) {
                $verifyEmail = User::where('email', $request->email)->first();

                if ($verifyEmail) {
                    if ($verifyEmail->id !== $user->id) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Email already in use'
                        ]);
                    }
                }
            }

            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->name = $request->name;
            $user->email = $request->email;
            $user->company_name = $request->company_name;
            $user->contact_name = $request->contact_name;
            $user->role = $request->role;
            $user->save();

            return response()->json([
                'user' => $user,
                'success' => true
            ]);
        } catch (Exception $e) {
            return ['error_message' => $e->getMessage()];
        }
    }

    /**
     * Remove the specified User along side all his/her resources (ie. Estimates, Invoices, Payments and Addresses)
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->deleteUser();
            return response()->json([
                'success' => true
            ]);
        } catch (Exception $e) {
            return ['error_message' => $e->getMessage()];
        }
    }


    /**
     * Remove a list of Users along side all their resources (ie. Estimates, Invoices, Payments and Addresses)
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        try {
            foreach ($request->id as $id) {
                $user = User::findOrFail($id);
                $user->deleteUser();
            }
            return response()->json([
                'success' => true
            ]);
        } catch (Exception $e) {
            return ['error_message' => $e->getMessage()];
        }
    }
}
