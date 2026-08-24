<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_shares', function (Blueprint $table): void {
            $table->id();
            $table->string('token', 80)->unique();
            $table->unsignedInteger('company_id')->index();
            $table->unsignedInteger('created_by')->nullable();
            $table->string('resource_type', 50)->index();
            $table->unsignedBigInteger('resource_id')->nullable()->index();
            $table->json('parameters')->nullable();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->timestamps();
        });

        $now = now();

        foreach (['invoices' => 'invoice', 'estimates' => 'estimate'] as $table => $type) {
            DB::table($table)
                ->whereNotNull('unique_hash')
                ->where('unique_hash', '!=', '')
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($type, $now): void {
                    foreach ($rows as $row) {
                        DB::table('public_shares')->insertOrIgnore([
                            'token' => $row->unique_hash,
                            'company_id' => $row->company_id,
                            'resource_type' => $type,
                            'resource_id' => $row->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('public_shares');
    }
};
