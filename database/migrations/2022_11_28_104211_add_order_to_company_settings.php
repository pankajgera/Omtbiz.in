<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddOrderToCompanySettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('companies')->pluck('id')->each(function ($companyId) {
            foreach (['order_prefix' => 'ORD', 'order_auto_generate' => 'YES'] as $option => $value) {
                DB::table('company_settings')->updateOrInsert(
                    ['option' => $option, 'company_id' => $companyId],
                    ['value' => $value, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        });
    }
}
