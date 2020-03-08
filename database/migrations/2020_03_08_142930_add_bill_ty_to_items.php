<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillTyToItems extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('bill_ty')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('bill_ty');
        });
    }
}
