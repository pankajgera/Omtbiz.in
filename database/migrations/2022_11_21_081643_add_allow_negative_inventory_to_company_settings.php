<?php

use App\Models\CompanySetting;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
        $companyIds = DB::table('companies')->pluck('id');
        if ($companyIds->isEmpty()) {
            return;
        }

        $now = Carbon::now();
        foreach ($companyIds as $companyId) {
            $exists = CompanySetting::where('option', 'allow_negative_inventory')
                ->where('company_id', $companyId)
                ->exists();
            if ($exists) {
                continue;
            }
            CompanySetting::create([
                'option' => 'allow_negative_inventory',
                'value' => 'NO',
                'company_id' => $companyId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
