<?php

namespace Tests\Feature;

use App\Http\Middleware\ConfigMiddleware;
use App\Models\Address;
use App\Models\AuditLog;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Orders;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserDeletionPreservationTest extends TestCase
{
    private const LINKED_TABLES = ['invoices', 'estimates', 'orders', 'payments', 'receipts', 'addresses', 'credits', 'debits'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->withoutMiddleware(ConfigMiddleware::class);
        config(['cache.default' => 'array']);
        Schema::enableForeignKeyConstraints();
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('role');
            $table->unsignedInteger('company_id')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
        foreach (self::LINKED_TABLES as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('reference')->unique();
            });
        }
        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->integer('quantity');
        });
        Schema::create('company_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('option');
            $table->string('value');
        });
        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->morphs('model');
            $table->string('collection_name');
        });
        (new \CreateAuditLogsTable)->up();
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Acting admin', 'email' => 'admin@example.test', 'role' => 'admin'],
            ['id' => 2, 'name' => 'Archived account', 'email' => 'archived@example.test', 'role' => 'accountant'],
            ['id' => 3, 'name' => 'Customer', 'email' => 'customer@example.test', 'role' => 'customer'],
        ]);
        foreach (self::LINKED_TABLES as $name) {
            DB::table($name)->insert([
                ['id' => 1, 'user_id' => 2, 'reference' => $name.'-1'],
                ['id' => 2, 'user_id' => 3, 'reference' => $name.'-2'],
            ]);
        }
        DB::table('invoice_items')->insert(['invoice_id' => 1, 'quantity' => 12]);
    }

    private function migrateProtection(): void
    {
        (require database_path('migrations/2026_09_28_000002_protect_records_from_user_deletion.php'))->up();
    }

    private function businessData(): array
    {
        $snapshot = [];
        foreach ([...self::LINKED_TABLES, 'invoice_items'] as $table) {
            $snapshot[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        }
        return $snapshot;
    }

    public function test_migration_preserves_all_rows_indexes_and_unrelated_constraints(): void
    {
        $before = $this->businessData();
        $this->migrateProtection();
        $this->migrateProtection();
        $this->assertSame($before, $this->businessData());
        foreach (self::LINKED_TABLES as $table) {
            $this->assertSame('restrict', Schema::getForeignKeys($table)[0]['on_delete']);
            $this->assertTrue(Schema::hasIndex($table, ['reference'], 'unique'));
        }
        $this->assertSame('cascade', Schema::getForeignKeys('invoice_items')[0]['on_delete']);
    }

    public function test_each_business_table_independently_blocks_direct_sql_user_deletion(): void
    {
        $this->migrateProtection();
        foreach (self::LINKED_TABLES as $index => $table) {
            $userId = 100 + $index;
            DB::table('users')->insert(['id' => $userId, 'name' => 'Linked user', 'email' => "$userId@example.test", 'role' => 'accountant']);
            DB::table($table)->insert(['user_id' => $userId, 'reference' => 'isolated-link']);
            $before = $this->businessData();
            try {
                DB::delete('DELETE FROM users WHERE id = ?', [$userId]);
                $this->fail("Expected $table to prevent permanent deletion");
            } catch (QueryException $exception) {
                $this->assertSame('23000', (string) $exception->getCode());
            }
            $this->assertDatabaseHas('users', ['id' => $userId]);
            $this->assertSame($before, $this->businessData());
        }
    }

    public function test_app_account_deletion_archives_only_account_and_preserves_historical_names(): void
    {
        $this->migrateProtection();
        $before = $this->businessData();
        $this->actingAs(User::findOrFail(1), 'api');
        $this->deleteJson('/api/users/2')->assertOk()->assertJson(['success' => true]);
        $this->assertSoftDeleted('users', ['id' => 2]);
        $this->assertSame($before, $this->businessData());
        $this->assertNull(User::find(2));
        $this->assertNull((new User)->findForPassport('archived@example.test'));
        foreach ([Invoice::class, Estimate::class, Orders::class, Payment::class, Receipt::class, Address::class, AuditLog::class] as $model) {
            $record = (new $model)->forceFill(['user_id' => 2]);
            $this->assertSame('Archived account', $record->user->name, $model);
        }
        $this->assertDatabaseHas('audit_logs', ['auditable_id' => 2, 'action' => 'deleted']);
    }

    public function test_bulk_account_deletion_preserves_all_business_data(): void
    {
        $this->migrateProtection();
        $before = $this->businessData();
        $this->actingAs(User::findOrFail(1), 'api');
        $this->postJson('/api/users/delete', ['id' => [2, 3]])->assertOk()->assertJson(['success' => true]);
        $this->assertSoftDeleted('users', ['id' => 2]);
        $this->assertSoftDeleted('users', ['id' => 3]);
        $this->assertSame($before, $this->businessData());
    }

    public function test_database_archiving_preserves_records_and_permanent_deletion_stays_blocked(): void
    {
        $this->migrateProtection();
        $before = $this->businessData();
        DB::table('users')->where('id', 2)->update(['deleted_at' => now()]);
        $this->assertNull(User::find(2));
        try {
            DB::table('users')->where('id', 2)->delete();
            $this->fail('Archived users with linked records must also be protected');
        } catch (QueryException $exception) {
            $this->assertSame('23000', (string) $exception->getCode());
        }
        $this->assertSame($before, $this->businessData());
    }
}
