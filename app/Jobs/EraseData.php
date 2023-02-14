<?php

namespace App\Jobs;

use App\Models\AccountLedger;
use App\Models\AccountMaster;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Estimate;
use App\Models\Dispatch;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Note;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Voucher;

class EraseData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //  All Estimates Delete
        $estimates = Estimate::all();
        $estimates->each(function ($estimate) {
            $estimate->forcedelete();
        });

        //  All Dispatches Delete
        $dispatches = Dispatch::all();
        $dispatches->each(function ($dispatch) {
            $dispatch->forcedelete();
        });

        //  All Invoice Item Delete
        $invoice_item = InvoiceItem::all();
        $invoice_item->each(function ($item) {
            $item->forcedelete();
        });

        //  All Invoice  Delete
        $invoices = Invoice::all();
        $invoices->each(function ($invoice) {
            $invoice->forcedelete();
        });

        //  All Inventory Item
        $inventory_items = InventoryItem::all();
        $inventory_items->each(function ($item) {
            $item->forcedelete();
        });

        //  All Inventory
        $inventories = Inventory::all();
        $inventories->each(function ($item) {
            $item->forcedelete();
        });


        //  All Items
        $items = Item::all();
        $items->each(function ($item) {
            $item->forcedelete();
        });

        //  All Notes
        $notes = Note::all();
        $notes->each(function ($item) {
            $item->forcedelete();
        });

        //  All Vouchers
        $vouchers = Voucher::all();
        $vouchers->each(function ($item) {
            $item->forcedelete();
        });

        //  All Orders Item
        $orders_items = OrderItems::all();
        $orders_items->each(function ($item) {
            $item->forcedelete();
        });

        //  All Orders Item
        $orders = Orders::all();
        $orders->each(function ($item) {
            $item->forcedelete();
        });

        //  All Payments
        $payments = Payment::all();
        $payments->each(function ($item) {
            $item->forcedelete();
        });

        //  All Receipts
        $receipts = Receipt::all();
        $receipts->each(function ($item) {
            $item->forcedelete();
        });

        //  All Account Masters
        $account_masters = AccountMaster::all();
        $account_masters->each(function ($item) {
            $item->forcedelete();
        });

        //  All Account Masters
        $account_ledgers = AccountLedger::all();
        $account_ledgers->each(function ($item) {
            $item->forcedelete();
        });
    }
}
