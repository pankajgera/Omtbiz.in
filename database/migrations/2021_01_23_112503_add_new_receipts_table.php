<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('receipt_number');
            $table->string('receipt_mode')->nullable();
            $table->date('receipt_date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('amount');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('invoice_id')->unsigned()->nullable();
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
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
        Schema::dropIfExists('receipts');
    }
}
