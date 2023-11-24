<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\EstimateTemplate;
use Carbon\Carbon;
use App\Http\Requests\EstimatesRequest;
use App\Models\Invoice;
use App\Models\Currency;
use App\Models\User;
use App\Models\Item;
use Validator;
use App\Models\CompanySetting;
use App\Models\Company;
use App\Mail\EstimatePdf;
use App\Models\AccountMaster;
use App\Models\Inventory;
use App\Notifications\EstimateSuccessful;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class EstimatesController extends Controller
{
    /**
     * Index page
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance')->get();
        $estimates_inprogress = Estimate::where('status', 'DRAFT')->with([
            'items',
            'master'
        ])->join('users', 'users.id', '=', 'estimates.user_id')
            ->applyFilters($request->only([
                'status',
                'customer_id',
                'estimate_number',
                'from_date',
                'to_date',
                'search',
                'orderByField',
                'orderBy'
            ]))
            ->whereCompany($request->header('company'), $request['filterBy'])
            ->select('estimates.*', 'users.name')
            ->latest()
            ->paginate($limit);

        $estimates_completed = Estimate::where('status', 'SENT')->with([
            'items',
            'master'
        ])->join('users', 'users.id', '=', 'estimates.user_id')
            ->applyFilters($request->only([
                'status',
                'customer_id',
                'estimate_number',
                'from_date',
                'to_date',
                'search',
                'orderByField',
                'orderBy'
            ]))
            ->whereCompany($request->header('company'), $request['filterBy'])
            ->select('estimates.*', 'users.name')
            ->latest()
            ->paginate($limit);

        $siteData = [
            'estimates_draft' => $estimates_inprogress,
            'estimates_sent' => $estimates_completed,
            'draft_count' => Estimate::where('status', 'DRAFT')->count(),
            'sent_count' => Estimate::where('status', 'SENT')->count(),
            'sundryDebtorsList' => $sundryDebtorsList,
        ];

        return response()->json($siteData);
    }

    /**
     * Create new estimate page data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $estimate_prefix = CompanySetting::getSetting('estimate_prefix', $request->header('company'));
        $estimate_num_auto_generate = CompanySetting::getSetting('estimate_auto_generate', $request->header('company'));

        $nextEstimateNumberAttribute = null;
        $nextEstimateNumber = Estimate::getNextEstimateNumber($estimate_prefix, $request->header('company'));

        if ($estimate_num_auto_generate == "YES") {
            $nextEstimateNumberAttribute = $nextEstimateNumber;
        }

        $customers = User::where('role', 'customer')->get();

        $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance')->get();

        return response()->json([
            'estimate_today_date' => Carbon::now()->toDateString(),
            'customers' => $customers,
            'nextEstimateNumberAttribute' => $nextEstimateNumberAttribute,
            'nextEstimateNumber' => $estimate_prefix . '-' . $nextEstimateNumber,
            'estimateTemplates' => EstimateTemplate::all(),
            'shareable_link' => '',
            'estimate_prefix' => $estimate_prefix,
            'sundryDebtorsList' => $sundryDebtorsList,
        ]);
    }

    /**
     * Create new estimate
     *
     * @param EstimatesRequest $request
     * @return JsonResponse
     */
    public function store(EstimatesRequest $request)
    {
        $estimate_number = $request->estimate_number;
        $number_attributes['estimate_number'] = $request->estimate_number;

        Validator::make($number_attributes, [
            'estimate_number' => 'required'
        ])->validate();

        //Check if same estimate number is already present
        //if YES, then add 1 to this estimate number
        $find_estimate = Estimate::where('estimate_number', '=', $estimate_number)->first();
        if (! empty($find_estimate)) {
            $estimate_prefix = CompanySetting::getSetting('estimate_prefix', $request->header('company'));
            $nextEstimateNumber = Estimate::getNextEstimateNumber($estimate_prefix, $request->header('company'));
            $number_attributes['estimate_number'] = $estimate_prefix . '-' . $nextEstimateNumber;
        }

        $estimate_date = Carbon::createFromFormat('d/m/Y', $request->estimate_date);
        $status = Estimate::DRAFT;

        $estimate = Estimate::create([
            'estimate_date' => $estimate_date,
            'expiry_date' => $estimate_date,
            'estimate_number' => $number_attributes['estimate_number'],
            'user_id' => $request->user_id,
            'company_id' => $request->header('company'),
            'estimate_template_id' => $request->estimate_template_id,
            'status' => $status,
            'sub_total' => $request->sub_total,
            'total' => $request->total,
            'notes' => $request->notes,
            'unique_hash' => str_random(60),
            'account_master_id' => $request->debtors['id'],
        ]);

        $estimateItems = $request->items;

        foreach ($estimateItems as $estimateItem) {
            $estimateItem['company_id'] = $request->header('company');
            $estimateItem['type'] = 'estimate';

            $item = $estimate->items()->create($estimateItem);
        }

        if ($request->has('estimateSend')) {
            $data['estimate'] = $estimate->toArray();
            $userId = $data['estimate']['user_id'];
            $data['user'] = User::find($userId)->toArray();
            $data['company'] = Company::find($estimate->company_id);
            $email = $data['user']['email'];
            $notificationEmail = CompanySetting::getSetting(
                'notification_email',
                $request->header('company')
            );

            if (!$email) {
                return response()->json([
                    'error' => 'user_email_does_not_exist'
                ]);
            }

            if (!$notificationEmail) {
                return response()->json([
                    'error' => 'notification_email_does_not_exist'
                ]);
            }

            \Mail::to($email)->send(new EstimatePdf($data, $notificationEmail));
        }

        $estimate = Estimate::with([
            'items',
            'user',
            'estimateTemplate',
        ])->find($estimate->id);

        // Notify user 
        auth()->user()->notify(new EstimateSuccessful($estimate));
        
        return response()->json([
            'estimate' => $estimate,
            'url' => url('/estimates/pdf/' . $estimate->unique_hash),
        ]);
    }

    /**
     * Show single estimate
     *
     * @param Request $request
     * @param  mixed $id
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        $estimate = Estimate::with([
            'items',
            'user',
            'estimateTemplate',
        ])->find($id);

        $siteData = [
            'estimate' => $estimate,
            'shareable_link' => url('/estimates/pdf/' . $estimate->unique_hash)
        ];

        return response()->json($siteData);
    }

    /**
     * Edit single estimate
     *
     * @param Request $request
     * @param  mixed $id
     * @return JsonResponse
     */
    public function edit(Request $request, $id)
    {
        $estimate = Estimate::with([
            'items',
            'user',
            'estimateTemplate',
        ])->find($id);
        $customers = User::where('role', 'customer')->get();
        $sundryDebtorsList = AccountMaster::where('id', $estimate->account_master_id)->select('id', 'name', 'opening_balance')->get();

        return response()->json([
            'customers' => $customers,
            'estimateNumber' => $estimate->getEstimateNumAttribute(),
            'estimate' => $estimate,
            'estimateTemplates' => EstimateTemplate::all(),
            'shareable_link' => url('/estimates/pdf/' . $estimate->unique_hash),
            'estimate_prefix' => $estimate->getEstimatePrefixAttribute(),
            'sundryDebtorsList' => $sundryDebtorsList,
        ]);
    }

    /**
     * Update single estimate
     *
     * @param EstimatesRequest $request
     * @param  mixed $id
     * @return JsonResponse
     */
    public function update(EstimatesRequest $request, $id)
    {
        $estimate = Estimate::findOrFail($id);
        $estimate->sub_total = $request->sub_total;
        $estimate->total = $request->total;
        $estimate->notes = $request->notes;
        $estimate->save();

        $oldItems = $estimate->items->toArray();
        $estimateItems = $request->items;

        foreach ($oldItems as $oldItem) {
            EstimateItem::destroy($oldItem['id']);
        }

        foreach ($estimateItems as $estimateItem) {
            $estimateItem['company_id'] = $request->header('company');
            $estimateItem['type'] = 'estimate';

            $item = $estimate->items()->create($estimateItem);
        }

        $estimate = Estimate::with([
            'items',
            'user',
            'estimateTemplate',
        ])->find($estimate->id);

        return response()->json([
            'estimate' => $estimate,
            'url' => url('/estimates/pdf/' . $estimate->unique_hash),
        ]);
    }

    /**
     * Delete single estimate
     *
     * @param  mixed $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
         // update notifications
         $notifications = auth()->user()->notifications()
         ->whereNull('read_at')
         ->orderBy('id', 'desc')
         ->get();

         foreach($notifications as $notifi) {
             $data = $notifi['data'];
             if($data['id'] === (int) $id) {
                 $notifi->update([
                     'read_at' => Carbon::now()
                 ]);

                 break;
             }
         }
        Estimate::deleteEstimate($id);

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Send estimate
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendEstimate(Request $request)
    {
        $estimate = Estimate::findOrFail($request->id);

        $data['estimate'] = $estimate->toArray();
        $userId = $data['estimate']['user_id'];
        $data['user'] = User::find($userId)->toArray();
        $data['company'] = Company::find($estimate->company_id);

        $email = $data['user']['email'];
        $notificationEmail = CompanySetting::getSetting(
            'notification_email',
            $request->header('company')
        );

        if (!$email) {
            return response()->json([
                'error' => 'user_email_does_not_exist'
            ]);
        }

        if (!$notificationEmail) {
            return response()->json([
                'error' => 'notification_email_does_not_exist'
            ]);
        }

        \Mail::to($email)->send(new EstimatePdf($data, $notificationEmail));

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Delete estimate
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        foreach ($request->id as $id) {
            Estimate::deleteEstimate($id);
        }

        return response()->json([
            'success' => true
        ]);
    }
}
