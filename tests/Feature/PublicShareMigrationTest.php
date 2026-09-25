<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicShareMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['invoices', 'estimates'] as $table) {
            Schema::create($table, function (Blueprint $blueprint): void {
                $blueprint->id();
                $blueprint->unsignedInteger('company_id');
                $blueprint->string('unique_hash')->nullable();
            });
        }
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('public_shares');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('estimates');

        parent::tearDown();
    }

    public function test_public_share_migration_backfills_legacy_links_and_rolls_back(): void
    {
        DB::table('invoices')->insert([
            'id' => 10,
            'company_id' => 7,
            'unique_hash' => 'existing-random-invoice-token',
        ]);
        DB::table('estimates')->insert([
            'id' => 11,
            'company_id' => 8,
            'unique_hash' => 'existing-random-estimate-token',
        ]);

        $migration = require database_path('migrations/2026_08_24_000001_create_public_shares_table.php');
        $migration->up();

        $this->assertDatabaseHas('public_shares', [
            'token' => 'existing-random-invoice-token',
            'company_id' => 7,
            'resource_type' => 'invoice',
            'resource_id' => 10,
        ]);
        $this->assertDatabaseHas('public_shares', [
            'token' => 'existing-random-estimate-token',
            'company_id' => 8,
            'resource_type' => 'estimate',
            'resource_id' => 11,
        ]);

        $migration->down();

        $this->assertFalse(Schema::hasTable('public_shares'));
    }
}
