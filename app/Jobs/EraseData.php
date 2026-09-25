<?php

namespace App\Jobs;

use App\Models\AccountLedger;
use App\Models\Dispatch;
use App\Models\Estimate;
use App\Models\EstimateItem;
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
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class EraseData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $companyId,
        public readonly int $initiatedBy
    ) {
    }

    public function handle(): void
    {
        DB::transaction(function (): void {
            $inventoryIds = Inventory::withoutGlobalScopes()
                ->where('company_id', $this->companyId)
                ->pluck('id');

            EstimateItem::withoutGlobalScopes()->where('company_id', $this->companyId)->delete();
            InvoiceItem::withoutGlobalScopes()->where('company_id', $this->companyId)->delete();
            OrderItems::withoutGlobalScopes()->where('company_id', $this->companyId)->delete();
            InventoryItem::withoutGlobalScopes()->whereIn('inventory_id', $inventoryIds)->delete();

            foreach ($this->companyOwnedModels() as $model) {
                $model::withoutGlobalScopes()
                    ->where('company_id', $this->companyId)
                    ->delete();
            }

            // AccountMaster is shared and has no company_id. Deleting it
            // would corrupt every other tenant.
            AccountLedger::withoutGlobalScopes()
                ->where('company_id', $this->companyId)
                ->delete();
        });
    }

    private function companyOwnedModels(): array
    {
        return [
            Dispatch::class,
            Estimate::class,
            Invoice::class,
            Inventory::class,
            Item::class,
            Note::class,
            Voucher::class,
            Orders::class,
            Payment::class,
            Receipt::class,
        ];
    }
}
