<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAccountLedgerForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_ledgers', function (Blueprint $table) {
            $table->dropForeign(['account_masters_id']);
            $table->foreign('account_master_id')
                ->references('id')
                ->on('account_masters')
                ->onDelete('cascade');
        });
    }
}
