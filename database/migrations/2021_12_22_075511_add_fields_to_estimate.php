<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToEstimate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->integer('account_master_id')->unsigned()->nullable();
            $table->foreign('account_master_id')->references('id')->on('account_masters')->onDelete('cascade');
        });
    }
}
