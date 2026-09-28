<?php

namespace Tests\Feature;

use App\Http\Middleware\ConfigMiddleware;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacyPassportLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->withoutMiddleware(ConfigMiddleware::class);
        config(['cache.default' => 'array']);
        $key = openssl_pkey_new(['private_key_bits' => 2048]);
        openssl_pkey_export($key, $privateKey);
        config([
            'passport.private_key' => $privateKey,
            'passport.public_key' => openssl_pkey_get_details($key)['key'],
        ]);
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('password');
            $table->string('role');
            $table->softDeletes();
        });
        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('secret')->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect')->default('');
            $table->boolean('personal_access_client')->default(false);
            $table->boolean('password_client')->default(false);
            $table->boolean('revoked')->default(false);
            $table->timestamps();
        });
        foreach (['000002_create_oauth_access_tokens', '000003_create_oauth_refresh_tokens'] as $migration) {
            (require base_path("vendor/laravel/passport/database/migrations/2016_06_01_{$migration}_table.php"))->up();
        }
        DB::table('users')->insert([
            ['id' => 2, 'email' => 'admin@example.test', 'password' => Hash::make('test-password'), 'role' => 'admin'],
            ['id' => 3, 'email' => 'other@example.test', 'password' => Hash::make('test-password'), 'role' => 'admin'],
        ]);
        DB::table('oauth_clients')->insert([
            'id' => 2, 'name' => 'Legacy login client', 'secret' => Hash::make('test-client-secret'),
            'password_client' => true,
        ]);
        Route::post('/api/auth-regression', fn () => response()->json([
            'id' => request()->user()->id, 'role' => request()->user()->role,
        ]))->middleware('auth:api');
    }

    private function issueToken(array $overrides = []): array
    {
        \Illuminate\Support\Once::flush();
        return $this->postJson('/oauth/token', array_replace([
            'grant_type' => 'password', 'client_id' => 2, 'client_secret' => 'test-client-secret',
            'username' => 'admin@example.test', 'password' => 'test-password',
        ], $overrides))->assertOk()->json();
    }

    private function checkToken(string $token)
    {
        // Each simulated HTTP request must get a fresh Passport client lookup.
        \Illuminate\Support\Once::flush();
        Auth::forgetGuards();
        return $this->postJson('/api/auth-regression', [], ['Authorization' => 'Bearer '.$token]);
    }

    public function test_admin_with_same_numeric_id_as_login_client_can_use_issued_token(): void
    {
        $this->migrateGrants();
        $token = $this->issueToken();
        $this->checkToken($token['access_token'])->assertOk()->assertJson(['id' => 2, 'role' => 'admin']);
    }

    private function migrateGrants(): void
    {
        (require database_path('migrations/2026_09_28_000001_define_legacy_oauth_client_grants.php'))->up();
    }

    public function test_existing_user_tokens_are_preserved_but_ambiguous_machine_tokens_are_rejected(): void
    {
        $user = $this->issueToken();
        $machine = $this->issueToken(['grant_type' => 'client_credentials']);
        $this->checkToken($user['access_token'])->assertUnauthorized();
        $this->checkToken($machine['access_token'])->assertUnauthorized();

        $this->migrateGrants();

        $this->checkToken($user['access_token'])->assertOk();
        $this->checkToken($machine['access_token'])->assertUnauthorized();
        $this->assertSame(1, DB::table('oauth_access_tokens')->whereNull('user_id')->where('revoked', true)->count());
    }

    public function test_other_admins_and_refresh_tokens_still_work(): void
    {
        $this->migrateGrants();
        $other = $this->issueToken(['username' => 'other@example.test']);
        $this->checkToken($other['access_token'])->assertOk()->assertJson(['id' => 3]);
        $original = $this->issueToken();
        $refreshed = $this->issueToken(['grant_type' => 'refresh_token', 'refresh_token' => $original['refresh_token']]);
        $this->checkToken($refreshed['access_token'])->assertOk()->assertJson(['id' => 2]);
    }

    public function test_login_client_no_longer_issues_machine_tokens_and_wrong_passwords_remain_rejected(): void
    {
        $this->migrateGrants();
        foreach ([['grant_type' => 'client_credentials'], ['grant_type' => 'password', 'password' => 'wrong-password']] as $request) {
            $this->postJson('/oauth/token', $request + [
                'client_id' => 2, 'client_secret' => 'test-client-secret',
                'username' => 'admin@example.test',
            ])->assertStatus(400)->assertJsonMissingPath('access_token');
        }
    }

    public function test_migration_preserves_explicit_grants_and_dedicated_machine_clients(): void
    {
        Schema::table('oauth_clients', fn (Blueprint $table) => $table->text('grant_types')->nullable());
        foreach ([
            [4, false, true, null],
            [5, false, false, null],
            [6, true, false, '["password","refresh_token"]'],
        ] as [$id, $password, $personal, $grants]) {
            DB::table('oauth_clients')->insert([
                'id' => $id, 'name' => 'Test client', 'secret' => Hash::make('test-client-secret'),
                'password_client' => $password, 'personal_access_client' => $personal, 'grant_types' => $grants,
            ]);
        }
        $this->migrateGrants();
        $this->migrateGrants();
        $this->assertSame(['personal_access', 'refresh_token'], json_decode(DB::table('oauth_clients')->where('id', 4)->value('grant_types'), true));
        $this->assertNull(DB::table('oauth_clients')->where('id', 5)->value('grant_types'));
        $this->assertSame('["password","refresh_token"]', DB::table('oauth_clients')->where('id', 6)->value('grant_types'));
        $machine = $this->issueToken(['client_id' => 5, 'grant_type' => 'client_credentials']);
        $this->checkToken($machine['access_token'])->assertUnauthorized();
    }
}
