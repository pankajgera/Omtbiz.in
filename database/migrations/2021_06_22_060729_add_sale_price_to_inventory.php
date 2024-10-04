<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalePriceToInventory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->bigInteger('sale_price')->unsigned()->default(0)->after('price');
        });
        Schema::table('inventories', function (Blueprint $table) {
            $table->bigInteger('sale_price')->unsigned()->default(0)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
           $table->dropColumn('sale_price');
        });
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('sale_price');
         });
    }
}
