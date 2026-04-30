<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PurgeSoftDeletedFinanceData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $cutoffDate = Carbon::now()->subDays(30);

        Invoice::onlyTrashed()
            ->where('deleted_at', '<=', $cutoffDate)
            ->chunkById(200, function ($invoices) {
                foreach ($invoices as $invoice) {
                    $invoice->forceDelete();
                }
            });

        Receipt::onlyTrashed()
            ->where('deleted_at', '<=', $cutoffDate)
            ->chunkById(200, function ($receipts) {
                foreach ($receipts as $receipt) {
                    $receipt->forceDelete();
                }
            });

        Voucher::onlyTrashed()
            ->where('deleted_at', '<=', $cutoffDate)
            ->chunkById(200, function ($vouchers) {
                foreach ($vouchers as $voucher) {
                    $voucher->forceDelete();
                }
            });
    }
}
