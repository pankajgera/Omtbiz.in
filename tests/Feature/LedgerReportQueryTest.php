<?php

namespace Tests\Feature;

use App\Models\AccountLedger;
use App\Models\CompanySetting;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LedgerReportQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        request()->attributes->remove('company_id');

        Schema::create('account_masters', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type')->default('Dr');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('account_ledgers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('account_master_id');
            $table->string('account');
            $table->string('type')->default('Dr');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('vouchers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('account_ledger_id');
            $table->string('account')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->dateTime('date');
            $table->string('related_voucher')->nullable();
            $table->string('voucher_type')->default('Invoice');
            $table->string('voucher_status')->nullable();
            $table->unsignedInteger('invoice_id')->nullable();
            $table->unsignedInteger('receipt_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('company_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('option');
            $table->string('value')->nullable();
            $table->timestamps();
        });

        DB::table('account_masters')->insert([['id' => 1, 'name' => 'Sales'], ['id' => 2, 'name' => 'Customer']]);
        DB::table('account_ledgers')->insert([
            ['id' => 1, 'company_id' => 1, 'account_master_id' => 1, 'account' => 'Sales'],
            ['id' => 2, 'company_id' => 1, 'account_master_id' => 2, 'account' => 'Customer'],
        ]);
    }

    public function test_ledger_with_more_linked_vouchers_than_one_query_allows_returns_every_row(): void
    {
        // 6,000 sales: 12,000 linked voucher ids, more than one chunk of the whereIn.
        $rows = [];
        for ($i = 1; $i <= 6000; $i++) {
            $sales = 2 * $i - 1;
            $party = 2 * $i;
            $date = $i <= 1000 ? '2025-12-15 00:00:00' : '2026-03-10 00:00:00';
            $related = "$sales, $party";
            $rows[] = ['id' => $sales, 'company_id' => 1, 'account_ledger_id' => 1, 'account' => 'Sales', 'debit' => 0, 'credit' => 100, 'date' => $date, 'related_voucher' => $related];
            $rows[] = ['id' => $party, 'company_id' => 1, 'account_ledger_id' => 2, 'account' => 'Customer', 'debit' => 100, 'credit' => 0, 'date' => $date, 'related_voucher' => $related];
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('vouchers')->insert($chunk);
        }

        $ledger = AccountLedger::withoutGlobalScopes()->find(1);
        $from = Carbon::parse('2026-01-01')->startOfDay();
        $to = Carbon::parse('2026-12-31')->endOfDay();

        $this->assertSame(5000, AccountLedger::countLedgerRows($ledger, $from, $to));

        $result = AccountLedger::ledgerMutation($ledger, $from, $to);

        $this->assertCount(5000, $result['related_vouchers']);
        $this->assertTrue($result['related_vouchers']->every(fn ($v) => (int) $v->account_ledger_id === 2));
        $this->assertEquals(500000, $result['current_balance_dr']);
        // The 1,000 sales before the period make up the opening balance.
        $this->assertEquals(100000, $result['total_opening_balance_dr']);
        $this->assertEquals(600000, $result['closing_balance_dr']);
    }

    public function test_a_saved_setting_is_used_immediately_despite_memoisation(): void
    {
        $this->assertSame('d M Y', CompanySetting::getSetting('carbon_date_format', 1));

        CompanySetting::setSetting('carbon_date_format', 'd-m-Y', 1);

        $this->assertSame('d-m-Y', CompanySetting::getSetting('carbon_date_format', 1));
    }
}
