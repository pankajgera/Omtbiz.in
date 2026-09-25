<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAllowNegativeInventoryToCompanySettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('companies')->pluck('id')->each(function ($companyId) {
            DB::table('company_settings')->updateOrInsert(
                ['option' => 'allow_negative_inventory', 'company_id' => $companyId],
                ['value' => 'NO', 'created_at' => now(), 'updated_at' => now()]
            );
        });
    }
}
