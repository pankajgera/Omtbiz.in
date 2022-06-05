<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalePriceToEstimateItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->bigInteger('sale_price')->unsigned()->default(0)->after('price');
        });
    }
}
