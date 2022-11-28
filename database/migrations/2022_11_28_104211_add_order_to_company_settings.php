<?php

use App\Models\CompanySetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderToCompanySettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("INSERT INTO
            `company_settings` (`id`, `option`, `value`, `company_id`, `created_at`, `updated_at`)
            VALUES
            (".intval(CompanySetting::max('id') + 1).", 'order_prefix', 'ORD', '1', '2022-11-28 11:11:11', '2022-11-28 11:11:11')");

        DB::statement("INSERT INTO
            `company_settings` (`id`, `option`, `value`, `company_id`, `created_at`, `updated_at`)
            VALUES
            (".intval(CompanySetting::max('id') + 1).", 'order_auto_generate', 'YES', '1', '2022-11-28 11:11:11', '2022-11-28 11:11:11')");
    }
}
