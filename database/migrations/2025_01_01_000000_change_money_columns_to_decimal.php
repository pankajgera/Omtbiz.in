<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMoneyColumnsToDecimal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->change();
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->change();
            $table->decimal('sale_price', 15, 2)->nullable()->change();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->change();
            $table->decimal('sale_price', 15, 2)->change();
            $table->decimal('tax', 15, 2)->change();
            $table->decimal('total', 15, 2)->change();
        });

        Schema::table('estimate_items', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->change();
            $table->decimal('sale_price', 15, 2)->change();
            $table->decimal('tax', 15, 2)->change();
            $table->decimal('total', 15, 2)->change();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('sub_total', 15, 2)->change();
            $table->decimal('total', 15, 2)->change();
            $table->decimal('tax', 15, 2)->change();
            $table->decimal('due_amount', 15, 2)->change();
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->decimal('sub_total', 15, 2)->change();
            $table->decimal('total', 15, 2)->change();
            $table->decimal('tax', 15, 2)->change();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->change();
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('price')->change();
            $table->string('sale_price')->nullable()->change();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->change();
            $table->unsignedBigInteger('sale_price')->change();
            $table->unsignedBigInteger('discount_val')->change();
            $table->unsignedBigInteger('tax')->change();
            $table->unsignedBigInteger('total')->change();
        });

        Schema::table('estimate_items', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->change();
            $table->unsignedBigInteger('sale_price')->change();
            $table->unsignedBigInteger('discount_val')->nullable()->change();
            $table->unsignedBigInteger('tax')->change();
            $table->unsignedBigInteger('total')->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->nullable()->change();
            $table->unsignedBigInteger('total')->nullable()->change();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('discount_val')->nullable()->change();
            $table->unsignedBigInteger('sub_total')->change();
            $table->unsignedBigInteger('total')->change();
            $table->unsignedBigInteger('tax')->change();
            $table->unsignedBigInteger('due_amount')->change();
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->unsignedBigInteger('discount_val')->nullable()->change();
            $table->unsignedBigInteger('sub_total')->change();
            $table->unsignedBigInteger('total')->change();
            $table->unsignedBigInteger('tax')->change();
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->unsignedBigInteger('amount')->change();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('amount')->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('amount')->change();
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('amount')->change();
        });
    }
}
