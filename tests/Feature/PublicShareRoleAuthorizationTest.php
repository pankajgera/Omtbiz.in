<?php

namespace Tests\Feature;

use App\Http\Middleware\ConfigMiddleware;
use App\Models\User;
use App\Support\PublicShareService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicShareRoleAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        config(['cache.default' => 'array']);
        $this->withoutMiddleware(ConfigMiddleware::class);
        Schema::create('public_shares', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->integer('company_id');
            $table->integer('created_by')->nullable();
            $table->string('resource_type');
            $table->integer('resource_id')->nullable();
            $table->json('parameters')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->string('unique_hash')->nullable();
            $table->timestamps();
        });
        DB::table('estimates')->insert(['id' => 1, 'company_id' => 7]);
    }

    private function signIn(string $role): void
    {
        $this->actingAs((new User)->forceFill(['id' => 42, 'company_id' => 7, 'role' => $role]), 'api');
    }

    public function test_estimate_user_cannot_issue_any_accounting_capability_or_enumerate_or_revoke_shares(): void
    {
        $this->signIn('estimate');
        foreach (array_merge(PublicShareService::DOCUMENT_TYPES, PublicShareService::REPORT_TYPES) as $type) {
            if ($type === 'estimate') continue;
            $this->postJson('/api/public-shares', ['type' => $type, 'resource_id' => 1, 'role' => 'admin'])->assertForbidden();
        }
        $this->getJson('/api/public-shares')->assertForbidden();
        $this->deleteJson('/api/public-shares/1')->assertForbidden();
        $this->assertDatabaseCount('public_shares', 0);
    }

    public function test_estimate_document_sharing_still_works_and_remains_company_bound(): void
    {
        $this->signIn('estimate');
        $this->postJson('/api/public-shares', ['type' => 'estimate', 'resource_id' => 1], ['company' => '999'])
            ->assertCreated()->assertJson(['type' => 'estimate', 'resource_id' => 1]);
        $this->assertDatabaseHas('public_shares', ['company_id' => 7, 'created_by' => 42, 'resource_type' => 'estimate']);
        DB::table('estimates')->insert(['id' => 2, 'company_id' => 999]);
        $this->postJson('/api/public-shares', ['type' => 'estimate', 'resource_id' => 2])->assertNotFound();
    }

    public function test_accounting_roles_can_issue_list_and_revoke_reports(): void
    {
        foreach (['admin', 'accountant'] as $role) {
            $this->signIn($role);
            $response = $this->postJson('/api/public-shares', ['type' => 'profit-loss', 'parameters' => [
                'from_date' => '01/01/2026', 'to_date' => '31/12/2026',
            ]])->assertCreated()->assertJson(['type' => 'report:profit-loss']);
            $this->getJson('/api/public-shares')->assertOk()->assertJsonCount(1, 'shares');
            $this->deleteJson('/api/public-shares/' . $response->json('id'))->assertOk();
            $this->getJson('/api/public-shares')->assertOk()->assertJsonCount(0, 'shares');
        }
    }
}
