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
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('discount_per_item');
            $table->dropColumn('discount_type');
            $table->dropColumn('discount');
            $table->dropColumn('discount_val');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('discount_type');
            $table->dropColumn('discount');
            $table->dropColumn('discount_val');
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn('discount_per_item');
            $table->dropColumn('discount_type');
            $table->dropColumn('discount');
            $table->dropColumn('discount_val');
        });

        Schema::table('estimate_items', function (Blueprint $table) {
            $table->dropColumn('discount_type');
            $table->dropColumn('discount');
            $table->dropColumn('discount_val');
        });
    }
};
