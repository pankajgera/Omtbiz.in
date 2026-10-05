<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One token per invoice form submission: a retried or double-sent save carries the
     * same token, so the second request returns the invoice already created instead of
     * creating a duplicate. Existing invoices keep NULL (allowed many times by the index).
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->string('submission_token', 64)->nullable()->after('unique_hash');
            $table->unique(['company_id', 'submission_token'], 'invoices_company_submission_token_unique');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropUnique('invoices_company_submission_token_unique');
            $table->dropColumn('submission_token');
        });
    }
};
