<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MarkExistingVouchersAsApproved extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('vouchers')
            ->where('voucher_type', 'Voucher')
            ->update([
                'voucher_status' => 'Done',
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Intentionally left blank; status backfill is irreversible safely.
    }
}
