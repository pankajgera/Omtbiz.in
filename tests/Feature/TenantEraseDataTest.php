<?php

namespace Tests\Feature;

use App\Jobs\EraseData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantEraseDataTest extends TestCase
{
    private const COMPANY_TABLES = [
        'account_ledgers',
        'dispatches',
        'estimates',
        'estimate_items',
        'inventories',
        'invoice_items',
        'invoices',
        'items',
        'notes',
        'order_items',
        'orders',
        'payments',
        'receipts',
        'vouchers',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        request()->attributes->remove('company_id');

        foreach (self::COMPANY_TABLES as $table) {
            Schema::create($table, function (Blueprint $definition): void {
                $definition->id();
                $definition->unsignedInteger('company_id');
            });
        }

        Schema::create('inventory_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('inventory_id');
        });

        Schema::create('account_masters', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('account_masters');

        foreach (array_reverse(self::COMPANY_TABLES) as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_job_erases_only_the_selected_company(): void
    {
        foreach (self::COMPANY_TABLES as $table) {
            DB::table($table)->insert([
                ['company_id' => 1],
                ['company_id' => 2],
            ]);
        }

        $companyOneInventory = DB::table('inventories')->where('company_id', 1)->value('id');
        $companyTwoInventory = DB::table('inventories')->where('company_id', 2)->value('id');
        DB::table('inventory_items')->insert([
            ['inventory_id' => $companyOneInventory],
            ['inventory_id' => $companyTwoInventory],
        ]);
        DB::table('account_masters')->insert(['name' => 'Shared master']);

        (new EraseData(1, 10))->handle();

        foreach (self::COMPANY_TABLES as $table) {
            $this->assertSame(0, DB::table($table)->where('company_id', 1)->count(), $table);
            $this->assertSame(1, DB::table($table)->where('company_id', 2)->count(), $table);
        }

        $this->assertSame(0, DB::table('inventory_items')->where('inventory_id', $companyOneInventory)->count());
        $this->assertSame(1, DB::table('inventory_items')->where('inventory_id', $companyTwoInventory)->count());
        $this->assertSame(1, DB::table('account_masters')->count());
    }
}
