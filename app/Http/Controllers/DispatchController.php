<?php

namespace App\Http\Controllers;

use App\Models\AccountMaster;
use Illuminate\Http\Request;
use App\Models\Dispatch;
use App\Models\Invoice;
use App\Models\Item;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Log;

class DispatchController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 100;

        $dispatch_inprogress = Dispatch::where('status', 'Draft')->applyFilters($request->only([
            'name',
            'from_date',
            'to_date',
            'transport',
            'orderByField',
            'orderBy',
            'status',
        ]))
            ->whereCompany($request->header('company'), $request['filterBy'])
            ->groupBy('invoice_id')
            ->latest()
            ->paginate($limit);

        foreach ($dispatch_inprogress as $inprogress) {
            $invoiceIds = array_filter(array_map('trim', explode(',', (string) $inprogress['invoice_id'])));
            $inprogress['invoices'] = Invoice::whereIn('id', $invoiceIds)->select('id', 'invoice_number', 'account_master_id')->get()->toArray();
            foreach ($inprogress['invoices'] as $each) {
                $inprogress['master'] = AccountMaster::where('id', $each['account_master_id'])->select('id', 'name', 'opening_balance')->first();
            }
        }

        $dispatch_completed = Dispatch::where('status', 'Sent')->applyFilters($request->only([
            'name',
            'from_date',
            'to_date',
            'transport',
            'orderByField',
            'orderBy',
        ]))
            ->whereCompany($request->header('company'), $request['filterBy'])
            ->groupBy('invoice_id')
            ->latest()
            ->paginate($limit);

        foreach ($dispatch_completed as $processed) {
            $invoiceIds = array_filter(array_map('trim', explode(',', (string) $processed['invoice_id'])));
            $processed['invoices'] = Invoice::whereIn('id', $invoiceIds)->with('master')->select('id', 'invoice_number', 'account_master_id')->get()->toArray();
            foreach ($processed['invoices'] as $each) {
                $processed['master'] = AccountMaster::where('id', $each['account_master_id'])->select('id', 'name', 'opening_balance')->first();
            }
        }
        $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance')->get();
        return response()->json([
            'dispatch_inprogress' => $dispatch_inprogress,
            'dispatch_completed' => $dispatch_completed,
            'dispatch_total' =>  Dispatch::count(),
            'sundryDebtorsList' => $sundryDebtorsList,
        ]);
    }

    /**
     * Summary counts for the Dispatch dashboard cards.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard(Request $request)
    {
        $company = $request->header('company');

        // No $filter arg to whereCompany() here on purpose - it would otherwise
        // restrict to *today's* dispatches only (see Dispatch::scopeWhereCompany).
        // These are all-time backlog counts, matching the Pending/Completed pages.
        //
        // distinct(invoice_id)->count() (rather than groupBy()->count(), which
        // returns per-group counts, not a total) so this matches the number of
        // rows a user actually sees on the To Be Dispatch / Dispatched tables.
        $pending = Dispatch::where('status', 'Draft')
            ->whereCompany($company)
            ->distinct('invoice_id')
            ->count('invoice_id');

        $dispatched = Dispatch::where('status', 'Sent')
            ->whereCompany($company)
            ->distinct('invoice_id')
            ->count('invoice_id');

        // Recent activity: last 5 dispatches sent out, last 5 newly added to
        // the pending queue - each enriched with invoice/party info the same
        // way the Pending/Completed list pages are.
        $recentDispatched = Dispatch::where('status', 'Sent')
            ->whereCompany($company)
            ->groupBy('invoice_id')
            ->orderByDesc('date_time')
            ->limit(5)
            ->get();

        $recentPending = Dispatch::where('status', 'Draft')
            ->whereCompany($company)
            ->groupBy('invoice_id')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        foreach ([$recentDispatched, $recentPending] as $list) {
            foreach ($list as $row) {
                $invoiceIds = array_filter(array_map('trim', explode(',', (string) $row->invoice_id)));
                $invoices = Invoice::whereIn('id', $invoiceIds)->select('id', 'invoice_number', 'account_master_id')->get();
                $row->invoices = $invoices;
                $row->master = $invoices->isNotEmpty()
                    ? AccountMaster::where('id', $invoices->first()->account_master_id)->select('id', 'name')->first()
                    : null;
            }
        }

        // Top 5 parties by how many dispatches are currently waiting to go out.
        // Invoice.status isn't reliably kept in sync with the dispatch flow on
        // older/imported data (most historical rows never got flipped to
        // TO_BE_DISPATCH), so this counts straight off the Draft dispatches
        // themselves - joining on the first invoice id in each dispatch's
        // (possibly comma-separated) invoice_id, since a bundled dispatch is
        // always one party (see the "party name should be same" rule used
        // when merging dispatches).
        $topPendingParties = DB::table('dispatches')
            ->join('invoices', DB::raw('CAST(SUBSTRING_INDEX(dispatches.invoice_id, \',\', 1) AS UNSIGNED)'), '=', 'invoices.id')
            ->where('dispatches.status', 'Draft')
            ->where('dispatches.company_id', $company)
            ->whereNotNull('invoices.account_master_id')
            ->select('invoices.account_master_id', DB::raw('count(*) as pending_count'))
            ->groupBy('invoices.account_master_id')
            ->orderByDesc('pending_count')
            ->limit(5)
            ->get();

        $partyNames = AccountMaster::whereIn('id', $topPendingParties->pluck('account_master_id'))
            ->select('id', 'name')
            ->get()
            ->keyBy('id');

        $topPendingParties = $topPendingParties->map(function ($row) use ($partyNames) {
            return [
                'account_master_id' => $row->account_master_id,
                'pending_count' => $row->pending_count,
                'name' => optional($partyNames->get($row->account_master_id))->name,
            ];
        })->values();

        // How stale is the pending backlog - bucketed by the dispatch's
        // date_time (the same field the Pending page's Today/Yesterday/etc.
        // quick-filter uses), so staff can see how much is genuinely old vs
        // recent rather than just one flat total.
        $weekAgo = Carbon::now('Asia/Kolkata')->subDays(7);
        $monthAgo = Carbon::now('Asia/Kolkata')->subDays(30);

        $pendingAging = [
            [
                'key' => 'recent',
                'label' => '0-7 days',
                'count' => Dispatch::where('status', 'Draft')
                    ->whereCompany($company)
                    ->where('dispatches.date_time', '>=', $weekAgo)
                    ->distinct('invoice_id')
                    ->count('invoice_id'),
            ],
            [
                'key' => 'month',
                'label' => '8-30 days',
                'count' => Dispatch::where('status', 'Draft')
                    ->whereCompany($company)
                    ->where('dispatches.date_time', '<', $weekAgo)
                    ->where('dispatches.date_time', '>=', $monthAgo)
                    ->distinct('invoice_id')
                    ->count('invoice_id'),
            ],
            [
                'key' => 'old',
                'label' => '31+ days',
                'count' => Dispatch::where('status', 'Draft')
                    ->whereCompany($company)
                    ->where('dispatches.date_time', '<', $monthAgo)
                    ->distinct('invoice_id')
                    ->count('invoice_id'),
            ],
        ];

        return response()->json([
            'pending_count' => $pending,
            'dispatched_count' => $dispatched,
            'total_count' => $pending + $dispatched,
            'recent_dispatched' => $recentDispatched,
            'recent_pending' => $recentPending,
            'top_pending_parties' => $topPendingParties,
            'pending_aging' => $pendingAging,
        ]);
    }

    /**
     * Paginated list of dispatches still pending (status = Draft), for the
     * standalone "To Be Dispatched" page.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pending(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 100;

        $query = Dispatch::where('dispatches.status', 'Draft')->applyFilters($request->only([
            'name',
            'from_date',
            'to_date',
            'orderByField',
            'orderBy',
        ]))
            // No $filter arg here on purpose: whereCompany() secretly restricts
            // to *today's* dispatches when $filter is falsy (see Dispatch::scopeWhereCompany).
            // Date scoping for this page is handled explicitly below instead.
            ->whereCompany($request->header('company'));

        // A search term looks across all pending dispatches regardless of
        // date (so searching an invoice number/party always finds it);
        // otherwise fall back to the named date-range filter, defaulting to
        // "today" per the page's default worklist view.
        if ($request->filled('search')) {
            $query->whereSearch($request->search);
        } else {
            $query->whereDateFilter($request->filled('date_filter') ? $request->date_filter : 'today');
        }

        $dispatch_inprogress = $query
            ->groupBy('dispatches.invoice_id')
            ->latest('dispatches.created_at')
            ->paginate($limit);

        foreach ($dispatch_inprogress as $inprogress) {
            $invoiceIds = array_filter(array_map('trim', explode(',', (string) $inprogress['invoice_id'])));
            $inprogress['invoices'] = Invoice::whereIn('id', $invoiceIds)->select('id', 'invoice_number', 'account_master_id')->get()->toArray();
            foreach ($inprogress['invoices'] as $each) {
                $inprogress['master'] = AccountMaster::where('id', $each['account_master_id'])->select('id', 'name', 'opening_balance')->first();
            }
        }

        $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance')->get();

        return response()->json([
            'dispatch_inprogress' => $dispatch_inprogress,
            'sundryDebtorsList' => $sundryDebtorsList,
        ]);
    }

    /**
     * Paginated list of dispatches already sent (status = Sent), for the
     * standalone "Completed Dispatch" page.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function completedList(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 100;

        $query = Dispatch::where('dispatches.status', 'Sent')->applyFilters($request->only([
            'name',
            'from_date',
            'to_date',
            'orderByField',
            'orderBy',
        ]))
            // No $filter arg here on purpose: whereCompany() secretly restricts
            // to *today's* dispatches when $filter is falsy (see Dispatch::scopeWhereCompany).
            // Date scoping for this page is handled explicitly below instead.
            ->whereCompany($request->header('company'));

        // Search here is invoice-number-only (unlike the Pending page, which
        // also matches party name); otherwise fall back to the named
        // date-range filter, defaulting to "today" to match Pending's page.
        if ($request->filled('search')) {
            $query->whereSearch($request->search, false);
        } else {
            $query->whereDateFilter($request->filled('date_filter') ? $request->date_filter : 'today');
        }

        $dispatch_completed = $query
            ->groupBy('dispatches.invoice_id')
            ->latest('dispatches.created_at')
            ->paginate($limit);

        foreach ($dispatch_completed as $processed) {
            $invoiceIds = array_filter(array_map('trim', explode(',', (string) $processed['invoice_id'])));
            $processed['invoices'] = Invoice::whereIn('id', $invoiceIds)->with('master')->select('id', 'invoice_number', 'account_master_id')->get()->toArray();
            foreach ($processed['invoices'] as $each) {
                $processed['master'] = AccountMaster::where('id', $each['account_master_id'])->select('id', 'name', 'opening_balance')->first();
            }
        }

        $sundryDebtorsList = AccountMaster::where('groups', 'like', 'Sundry Debtors')->select('id', 'name', 'opening_balance')->get();

        return response()->json([
            'dispatch_completed' => $dispatch_completed,
            'sundryDebtorsList' => $sundryDebtorsList,
        ]);
    }

    /**
     * Edit Dispatch
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, $id)
    {
        $dispatch = Dispatch::find($id);
        $dispatch['invoice_id'] = explode(', ', $dispatch->invoice_id);

        return response()->json([
            'dispatch' => $dispatch,
        ]);
    }

    /**
     * Edit To Be Dispatch
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function tobeEdit(Request $request)
    {
        $to_be_dispatch_ids = array_unique(explode(',', $request->getContent()));
        $tobeDispatch = [];
        foreach ($to_be_dispatch_ids as $id) {
            $dispatch = Dispatch::find($id);
            $dispatch['invoice_id'] = explode(', ', $dispatch->invoice_id);
            array_push($tobeDispatch, $dispatch);
        }

        return response()->json([
            'dispatch' => $tobeDispatch,
        ]);
    }

    /**
     * Create Dispatch.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $date_format = 'Y-m-d\TH:i:s.v\Z';
            if (strpos($request->date_time, ' ') !== false) {
                $date_format = 'Y-m-d H:i:s';
            }
            $date = Carbon::createFromFormat($date_format, $request->date_time);
            $date->setTimeZone('Asia/Kolkata');
            $invoiceIds = array_values(array_unique(array_map('intval', (array) $request->invoice_id)));
            $invoices = Invoice::whereIn('id', $invoiceIds)->get();

            $dispatch = new Dispatch();
            $dispatch->invoice_id = implode(', ', $invoiceIds);
            $dispatch->name = $invoices->pluck('invoice_number')->unique()->implode(', ');
            $dispatch->date_time = $date;
            $dispatch->transport = $request->transport;
            $dispatch->person = $request->person;
            $dispatch->time = $request->time;
            $dispatch->status = $request->status['name'];
            $dispatch->company_id = $request->header('company');
            $dispatch->save();

            foreach ($invoices as $each) {
                $deleteing_disptach = Dispatch::where('invoice_id', $each->id)
                    ->where('id', '!=', $dispatch->id)
                    ->first();
                if ($deleteing_disptach) {
                    $deleteing_disptach->delete();
                }

                $each->update([
                    'dispatch_id' => $dispatch->id,
                    'paid_status' => 'DISPATCHED',
                    'status' => $request->status['name'] === 'Sent' ? 'COMPLETED' : 'TO_BE_DISPATCH',
                ]);
            }
            if ('Sent' === $dispatch->status) {
                $dispatch->addDispatchBillTy($dispatch, $invoices->sum('total'), $request->header('company'), []);
            }
            $invoices_master_id = AccountMaster::where('groups', 'Sundry Debtors')->get();
            return response()->json([
                'dispatch' => $dispatch,
                'invoices' => $invoices_master_id,
            ]);
        } catch (Exception $e) {
            Log::error('Error while saving dispatch', [$e]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update an existing Dispatch.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDispatch(Request $request, $id)
    {
        try {
            $date_format = 'Y-m-d\TH:i:s.v\Z';
            if (strpos($request->date_time, ' ') !== false) {
                $date_format = 'Y-m-d H:i:s';
            }
            $date = Carbon::createFromFormat($date_format, $request->date_time);
            $date->setTimeZone('Asia/Kolkata');

            //Selected dispatch might have multiple invoices
            $same_invoice_dispatch = Dispatch::whereIn('invoice_id', [Dispatch::where('id', $id)->value('invoice_id')])->get();

            foreach ($same_invoice_dispatch as $dispatch) {
                //$dispatch->name = null;
                $dispatch->invoice_id = implode(', ', $request->invoice_id);
                $dispatch->date_time = $date;
                $dispatch->transport = $request->transport;
                $dispatch->person = $request->person;
                $dispatch->time = $request->time;
                $dispatch->status = $request->status['name'];
                $dispatch->company_id = $request->header('company');
                $dispatch->save();

                $invoices = Invoice::whereIn('id', $request->invoice_id)->get();
                foreach ($invoices as $each) {
                    $each->status = 'COMPLETED';
                    $each->save();
                    if (!$dispatch->name) {
                        $dispatch->update([
                            'name' => $each->invoice_number,
                        ]);
                    } else {
                        if (false === strpos($dispatch->name, $each->invoice_number)) {
                            $dispatch->update([
                                'name' => $dispatch->name . ', ' . $each->invoice_number,
                            ]);
                        }
                    }
                }
                if ('Sent' === $dispatch->status) {
                    $dispatch->addDispatchBillTy($dispatch, $invoices->sum('total'), $request->header('company'), []);
                } else {
                    $dispatch->removeDispatchBillTy($dispatch);
                }
            }

            return response()->json([
                'dispatch' => $dispatch,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating dispatch', [$e]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update an existing To Be Dispatch.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateToBeDispatch(Request $request)
    {
        $all_selected_dispatch = Dispatch::whereIn('id', $request->all_selected_dispatch)->get();
        //Selected dispatch might have multiple invoices
        $same_invoice_dispatch = Dispatch::whereIn('invoice_id', $all_selected_dispatch->pluck('invoice_id')->toArray())->get();
        try {
            foreach ($same_invoice_dispatch as $each) {
                $date_format = 'Y-m-d\TH:i:s.v\Z';
                if (strpos($request->date_time, ' ') !== false) {
                    $date_format = 'Y-m-d H:i:s';
                }
                $date = Carbon::createFromFormat($date_format, $request->date_time);
                $date->setTimeZone('Asia/Kolkata');
                $each->update([
                    //'name' => $request->name,
                    'invoice_id' => implode(', ', $request->invoice_id),
                    'date_time' => $date,
                    'transport' => $request->transport,
                    'person' => $request->person,
                    'time' => $request->time,
                    'status' => $request->status['name'],
                ]);
                $invoices = Invoice::whereIn('id', $request->invoice_id)->get();
                if ('Sent' === $each->status) {
                    foreach ($invoices as $invoice) {
                        $invoice->update([
                            'dispatch_id' => $each->id,
                            'paid_status' => 'DISPATCHED',
                            'status' => 'COMPLETED',
                        ]);
                    }
                    $each->addDispatchBillTy($each, $invoices->sum('total'), $request->header('company'), []);
                } else {
                    foreach ($invoices as $invoice) {
                        $invoice->update([
                            'status' => 'TO_BE_DISPATCH',
                            'paid_status' => 'TO_BE_DISPATCH',
                        ]);
                    }
                    $each->removeDispatchBillTy($each);
                }
            }
            return response()->json([
                'dispatch' => $all_selected_dispatch,
            ]);
        } catch (Exception $e) {
            Log::error('Error while updating dispatch', [$e]);
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete an existing Dispatch.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        Dispatch::deleteDispatch($id);
        return response()->json(['dispatch' => true]);
    }


    /**
     * Delete a list of existing Dispatch.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        foreach ($request->id as $id) {
            Dispatch::deleteDispatch($id);
        }

        return response()->json(['dispatch' => true]);
    }

    /**
     * Move a list of existing Dispatch.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function multiple(Request $request)
    {
        foreach ($request->id as $id) {
            Dispatch::moveDispatch($id, $request->header('company'));
        }

        return response()->json(204);
    }


    /**
     * Retrive a specified user's unpaid invoices from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvoices(Request $request)
    {
        // This used to load every still-pending invoice unbounded (a prior
        // fix removed a ->limit(200) specifically to stop hiding older
        // eligible bills). For a company with tens of thousands of pending
        // invoices, that now blows PHP's memory limit (500) or, once memory
        // is raised, just takes too long and the gateway times out (504).
        //
        // Fix: make this search-driven and capped instead of "load everything".
        // - search: filter by invoice number or party name (the frontend's
        //   invoice picker now queries this as the user types).
        // - include_ids: invoice(s) already attached to the dispatch being
        //   edited are always returned regardless of status/search/limit, so
        //   the edit form can still resolve and display them. Queried
        //   separately from the capped/ordered list below and merged in -
        //   an earlier version OR'd this into the same query, but a company
        //   with enough newer pending invoices could push the specifically
        //   requested id(s) outside the LIMIT window before the OR ever
        //   mattered.
        // - limit: hard cap so this endpoint can never again return an
        //   unbounded result set, default 50.
        $company = $request->header('company');
        $search = trim((string) $request->query('search', ''));
        $includeIds = array_filter(array_map('intval', explode(',', (string) $request->query('include_ids'))));
        $limit = (int) $request->query('limit', 50);

        $invoices = Invoice::with('master')
            ->whereCompany($company)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'COMPLETED');
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($match) use ($search) {
                    $match->where('invoice_number', 'like', '%' . $search . '%')
                        ->orWhereHas('master', function ($masterQuery) use ($search) {
                            $masterQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        if (! empty($includeIds)) {
            $existingIds = $invoices->pluck('id')->all();
            $missingIds = array_diff($includeIds, $existingIds);
            if (! empty($missingIds)) {
                $pinned = Invoice::with('master')
                    ->whereCompany($company)
                    ->whereIn('id', $missingIds)
                    ->get();
                $invoices = $invoices->concat($pinned)->values();
            }
        }

        return response()->json([
            'invoices' => $invoices
        ]);
    }
}
