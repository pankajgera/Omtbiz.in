<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAccountLedgersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_ledgers', function (Blueprint $table) {
            $table->dropForeign('account_ledgers_voucher_id_foreign');
            $table->dropColumn('voucher_id');
            $table->string('type')->nullable()->change();
            $table->string('bill_no')->nullable()->change();
            $table->string('account')->nullable()->change();
            $table->string('short_narration')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
