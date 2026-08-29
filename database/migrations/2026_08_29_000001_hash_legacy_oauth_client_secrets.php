<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Passport 13 validates OAuth client secrets as password hashes. Clients
     * created by older Passport releases stored those secrets as plain text.
     */
    public function up(): void
    {
        if (! Schema::hasTable('oauth_clients') || ! Schema::hasColumn('oauth_clients', 'secret')) {
            return;
        }

        DB::table('oauth_clients')
            ->whereNotNull('secret')
            ->where('secret', '!=', '')
            ->orderBy('id')
            ->chunkById(100, function ($clients): void {
                foreach ($clients as $client) {
                    if (Hash::isHashed($client->secret)) {
                        continue;
                    }

                    DB::table('oauth_clients')
                        ->where('id', $client->id)
                        ->update(['secret' => Hash::make($client->secret)]);
                }
            });
    }

    public function down(): void
    {
        // Password hashes are intentionally irreversible. Existing client
        // secrets remain compatible with both current and future Passport use.
    }
};
