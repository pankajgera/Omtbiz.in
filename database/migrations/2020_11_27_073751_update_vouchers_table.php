<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->integer('account_ledger_id')->unsigned();
            $table->foreign('account_ledger_id')->references('id')->on('account_ledgers');
            $table->dateTime('date');
            $table->enum('type', ['D','C'])->default('D');
            $table->string('debit_amount')->nullable()->change();
            $table->string('credit_amount')->nullable()->change();
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
