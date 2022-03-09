<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeVouchersForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign(['account_master_id']);
            $table->foreign('account_master_id')
                ->references('id')
                ->on('account_masters')
                ->onDelete('cascade');

            $table->dropForeign(['account_ledger_id']);
            $table->foreign('account_ledger_id')
                ->references('id')
                ->on('account_ledgers')
                ->onDelete('cascade');
        });
    }
}
