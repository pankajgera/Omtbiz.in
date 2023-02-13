<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('inventory_id')->unsigned();
            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');
            $table->string('quantity');
            $table->string('price');
            $table->string('sale_price')->nullable();
            $table->string('unit');
            $table->string('worker_name')->nullable();
            $table->timestamps();
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->dropColumn('worker_name');
            $table->dropColumn('price');
            $table->dropColumn('sale_price');
            $table->dropColumn('unit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventories_items');
    }
};
