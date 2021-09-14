<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDispatchInvoiceId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('dispatches', function (Blueprint $table) {
            $table->dropForeign('dispatches_invoice_id_foreign');
            $table->dropColumn('invoice_id');
        });

        Schema::table('dispatches', function (Blueprint $table) {
            $table->string('invoice_id')->after('name');
        });
    }
}
