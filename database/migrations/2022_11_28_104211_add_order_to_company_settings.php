<?php

use App\Models\CompanySetting;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
        $companyIds = DB::table('companies')->pluck('id');
        if ($companyIds->isEmpty()) {
            return;
        }

        $now = Carbon::now();
        foreach ($companyIds as $companyId) {
            $existsPrefix = CompanySetting::where('option', 'order_prefix')
                ->where('company_id', $companyId)
                ->exists();
            if (! $existsPrefix) {
                CompanySetting::create([
                    'option' => 'order_prefix',
                    'value' => 'ORD',
                    'company_id' => $companyId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $existsAuto = CompanySetting::where('option', 'order_auto_generate')
                ->where('company_id', $companyId)
                ->exists();
            if (! $existsAuto) {
                CompanySetting::create([
                    'option' => 'order_auto_generate',
                    'value' => 'YES',
                    'company_id' => $companyId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
