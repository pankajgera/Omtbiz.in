<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeInvoiceIndirectValuesToDecimal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('indirect_income_value', 15, 2)->nullable()->change();
            $table->decimal('indirect_expense_value', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('indirect_income_value')->nullable()->change();
            $table->unsignedBigInteger('indirect_expense_value')->nullable()->change();
        });
    }
}
