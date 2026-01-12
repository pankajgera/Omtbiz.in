<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceEstimateReferenceIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['company_id', 'invoice_date', 'account_master_id'], 'invoices_company_date_account_idx');
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->index(['company_id', 'estimate_date', 'account_master_id'], 'estimates_company_date_account_idx');
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
            $table->dropIndex('invoices_company_date_account_idx');
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->dropIndex('estimates_company_date_account_idx');
        });
    }
}
