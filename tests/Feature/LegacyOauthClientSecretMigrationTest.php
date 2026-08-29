<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Bridge\ClientRepository as PassportClientValidator;
use Tests\TestCase;

class LegacyOauthClientSecretMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('oauth_clients', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('secret')->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect');
            $table->boolean('personal_access_client')->default(false);
            $table->boolean('password_client')->default(false);
            $table->boolean('revoked')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('oauth_clients');

        parent::tearDown();
    }

    public function test_it_hashes_plaintext_secrets_without_rehashing_existing_hashes(): void
    {
        $existingHash = Hash::make('already-secure-secret');

        DB::table('oauth_clients')->insert([
            ['id' => 1, 'name' => 'Legacy password client', 'secret' => 'legacy-plain-secret', 'redirect' => 'http://localhost', 'password_client' => true],
            ['id' => 2, 'name' => 'Current password client', 'secret' => $existingHash, 'redirect' => 'http://localhost', 'password_client' => true],
            ['id' => 3, 'name' => 'Public client', 'secret' => null, 'redirect' => 'http://localhost', 'password_client' => false],
        ]);

        $migration = require database_path('migrations/2026_08_29_000001_hash_legacy_oauth_client_secrets.php');
        $migration->up();

        $legacySecret = DB::table('oauth_clients')->where('id', 1)->value('secret');

        $this->assertNotSame('legacy-plain-secret', $legacySecret);
        $this->assertTrue(Hash::check('legacy-plain-secret', $legacySecret));
        $this->assertTrue(app(PassportClientValidator::class)->validateClient('1', 'legacy-plain-secret', 'password'));
        $this->assertSame($existingHash, DB::table('oauth_clients')->where('id', 2)->value('secret'));
        $this->assertNull(DB::table('oauth_clients')->where('id', 3)->value('secret'));

        // Re-running the migration must not invalidate the configured secret.
        $migration->up();
        $this->assertSame($legacySecret, DB::table('oauth_clients')->where('id', 1)->value('secret'));
    }
}
