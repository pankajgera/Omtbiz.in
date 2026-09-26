<?php

namespace Tests\Feature;

use App\Http\Controllers\BanksController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BankListTest extends TestCase
{
    private bool $bankTableCreated = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        require_once database_path('migrations/2020_12_29_114943_add_bank_table.php');
        (new \AddBankTable)->up();
        $this->bankTableCreated = true;
        DB::table('bank')->insert([
            ['name' => 'First Bank', 'amount' => '100', 'date' => '2026-09-01 00:00:00'],
            ['name' => 'Second Bank', 'amount' => '250', 'date' => '2026-09-26 00:00:00'],
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->bankTableCreated) {
            Schema::dropIfExists('bank');
        }
        parent::tearDown();
    }

    public function test_list_uses_the_existing_singular_table_and_pagination_contract(): void
    {
        $response = (new BanksController)->index(Request::create('/api/banks', 'GET', [
            'orderByField' => 'name', 'orderBy' => 'asc', 'limit' => 1,
        ]))->getData(true);

        $this->assertSame(2, $response['banks']['total']);
        $this->assertSame('First Bank', $response['banks']['data'][0]['name']);
        $this->assertSame(2, $response['banks']['last_page']);
    }

    public function test_name_amount_and_date_filters_use_supported_columns(): void
    {
        $response = (new BanksController)->index(Request::create('/api/banks', 'GET', [
            'name' => 'Second', 'amount' => '250', 'date' => '2026-09-26',
            'orderByField' => 'missing_column',
        ]))->getData(true);

        $this->assertSame(1, $response['banks']['total']);
        $this->assertSame('Second Bank', $response['banks']['data'][0]['name']);
    }
}
