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
use Crater\Http\Requests\UserRequest;

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
        $limit = $request->has('limit') ? $request->limit : 10;

        $users = User::user()
            ->applyFilters($request->only([
                'search',
                'contact_name',
                'display_name',
                'phone',
                'orderByField',
                'orderBy'
            ]))
            ->whereCompany($request->header('company'))
            ->select('users.*',
                DB::raw('sum(invoices.due_amount) as due_amount')
            )
            ->groupBy('users.id')
            ->leftJoin('invoices', 'users.id', '=', 'invoices.user_id')
            ->paginate($limit);

        $siteData = [
            'users' => $users
        ];

        return response()->json($siteData);
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
            $user->currency_id = $request->currency_id;
            $user->company_id = $request->header('company');
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->company_name = $request->company_name;
            $user->contact_name = $request->contact_name;
            $user->website = $request->website;
            $user->enable_portal = $request->enable_portal;
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

            return response()->json([
                'user' => $user
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
            $user->currency_id = $request->currency_id;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->company_name = $request->company_name;
            $user->contact_name = $request->contact_name;
            $user->website = $request->website;
            $user->enable_portal = $request->enable_portal;
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
            User::deleteUser($id);

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
                User::deleteUser($id);
            }
            return response()->json([
                'success' => true
            ]);
        } catch (Exception $e) {
            return ['error_message' => $e->getMessage()];
        }
    }

    // public function getAddUser(Request $request)
    // {
    //     $companies = Company::all()->toArray();

    //     $roles = Role::all()->toArray();

    //     return response()->json([
    //         'companies' => $companies,
    //         'roles' => $roles
    //     ]);
    // }

    // public function updateAddUser(Request $request)
    // {
    //     try {
    //         $user = User::updateOrCreate(
    //             ['email' => $request->email, 'role' => $request->role['name'], 'company_id' => $request->company['id']],
    //             ['name' => $request->name, 'company_name' => $request->company['name'], 'password' => Hash::make($request->password)]
    //         );
    //         return response()->json([
    //             'user' => $user,
    //             'success' => true
    //         ]);
    //     } catch (Exception $e) {
    //         throw ValidationException::withMessages(['field' => ['Error while adding new user '. $e]]);
    //     }
    // }
}
