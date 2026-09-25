<?php

namespace Tests\Feature;

use App\Http\Controllers\DispatchController;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DispatchListTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-09-26 12:00:00', 'Asia/Kolkata'));
        request()->attributes->set('company_id', 7);
        DB::connection()->getPdo()->sqliteCreateFunction('DATE_FORMAT', fn ($value, $format) => substr($value, 0, 10));

        Schema::create('dispatches', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('invoice_id');
            $table->string('status');
            $table->string('transport');
            $table->dateTime('date_time');
            $table->timestamps();
        });
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('invoice_number');
            $table->unsignedInteger('account_master_id');
        });
        Schema::create('account_masters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('name');
            $table->string('groups');
            $table->integer('opening_balance');
        });
        foreach ([
            [1, 7, 'Draft', '2026-08-01 10:00:00'],
            [2, 7, 'Sent', '2026-09-25 10:00:00'],
            [3, 7, 'Draft', '2026-09-26 09:00:00'],
            [4, 99, 'Draft', '2026-08-01 10:00:00'],
            [5, 99, 'Sent', '2026-09-25 10:00:00'],
        ] as [$id, $company, $status, $date]) {
            DB::table('dispatches')->insert([
                'id' => $id, 'company_id' => $company, 'invoice_id' => (string) $id,
                'status' => $status, 'date_time' => $date, 'transport' => 'Road',
                'created_at' => $date, 'updated_at' => $date,
            ]);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Schema::dropIfExists('dispatches');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('account_masters');
        parent::tearDown();
    }

    private function listing(array $filters = []): array
    {
        $request = Request::create('/api/dispatch', 'GET', $filters + [
            'filterBy' => 'false', 'orderByField' => 'created_at', 'orderBy' => 'desc',
        ]);
        $request->headers->set('company', '7');

        return app(DispatchController::class)->index($request)->getData(true);
    }

    public function test_default_lists_include_older_dispatches_only_for_the_current_company(): void
    {
        $data = $this->listing();
        $this->assertSame([3, 1], array_column($data['dispatch_inprogress']['data'], 'id'));
        $this->assertSame([2], array_column($data['dispatch_completed']['data'], 'id'));
    }

    public function test_explicit_date_range_still_filters_both_lists(): void
    {
        $data = $this->listing(['from_date' => '25/09/2026', 'to_date' => '25/09/2026', 'filterBy' => 'true']);
        $this->assertSame([], $data['dispatch_inprogress']['data']);
        $this->assertSame([2], array_column($data['dispatch_completed']['data'], 'id'));
    }

    public function test_backlog_remains_paginated(): void
    {
        $data = $this->listing(['limit' => 1]);
        $this->assertCount(1, $data['dispatch_inprogress']['data']);
        $this->assertSame(2, $data['dispatch_inprogress']['total']);
        $this->assertSame(2, $data['dispatch_inprogress']['last_page']);
    }
}
