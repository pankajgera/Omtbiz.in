<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables whose rows are soft-deleted when an invoice, voucher, receipt or
     * payment is sent to the recycle bin. Invoice items travel with their invoice.
     */
    private array $softDeleteTables = ['invoices', 'invoice_items', 'vouchers', 'receipts', 'payments'];

    public function up(): void
    {
        foreach ($this->softDeleteTables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->softDeletes()->index();
                });
            }
        }

        // One row per user-facing delete. `meta` records exactly which rows were
        // trashed and which side effects ran, so a restore can reverse them.
        Schema::create('recycle_bin_entries', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id')->index();
            $table->string('resource_type', 20);
            $table->unsignedBigInteger('resource_id');
            $table->string('label')->nullable();
            $table->string('party')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->json('meta');
            // Plain id rather than a foreign key: users referenced by business
            // records are protected from deletion, and a trash entry must not block that.
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->index();
            $table->timestamps();

            $table->index(['resource_type', 'resource_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recycle_bin_entries');

        foreach ($this->softDeleteTables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->dropSoftDeletes();
                });
            }
        }
    }
};
