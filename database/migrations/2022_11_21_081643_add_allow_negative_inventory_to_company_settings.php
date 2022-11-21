<?php

use App\Models\CompanySetting;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllowNegativeInventoryToCompanySettings extends Migration
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
            (".intval(CompanySetting::max('id') + 1).", 'allow_negative_inventory', 'NO', '1', '2022-11-20 11:11:11', '2022-11-20 11:11:11')");
    }
}
