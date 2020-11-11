<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['D','C'])->default('D');
            $table->integer('account_master_id')->unsigned();
            $table->foreign('account_master_id')->references('id')->on('account_masters');
            $table->string('account');
            $table->string('debit_amount');
            $table->string('credit_amount');
            $table->string('short_narration');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
}
