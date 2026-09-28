<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('oauth_clients')) {
            return;
        }

        if (! Schema::hasColumn('oauth_clients', 'grant_types')) {
            Schema::table('oauth_clients', function (Blueprint $table): void {
                $table->text('grant_types')->nullable();
            });
        }

        // Passport 13 otherwise infers client_credentials for legacy login
        // clients, rejecting real users whose numeric ID equals the client ID.
        DB::table('oauth_clients')->whereNull('grant_types')->orderBy('id')
            ->chunkById(100, function ($clients): void {
                foreach ($clients as $client) {
                    $grants = [];
                    if ($client->password_client ?? false) {
                        $grants[] = 'password';
                    }
                    if ($client->personal_access_client ?? false) {
                        $grants[] = 'personal_access';
                    }
                    if (! $grants) {
                        continue;
                    }
                    $grants[] = 'refresh_token';

                    DB::transaction(function () use ($client, $grants): void {
                        // Previously issued machine tokens have no user_id.
                        // Revoke them before changing the client's grants so
                        // they can never be mistaken for a matching user.
                        if (Schema::hasTable('oauth_access_tokens')) {
                            DB::table('oauth_access_tokens')
                                ->where('client_id', $client->id)
                                ->whereNull('user_id')
                                ->update(['revoked' => true]);
                        }

                        DB::table('oauth_clients')->where('id', $client->id)
                            ->update(['grant_types' => json_encode($grants)]);
                    });
                }
            });
    }

    public function down(): void
    {
        // Keep explicit grants: removing them reintroduces ambiguous tokens.
    }
};
