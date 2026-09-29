<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\RecycleBinEntry;
use App\Models\Voucher;
use App\Services\RecycleBin;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RecycleBinTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        request()->attributes->remove('company_id');

        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('invoice_number');
            $table->unsignedInteger('account_master_id')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('invoice_id');
            $table->unsignedInteger('inventory_id');
            $table->decimal('quantity', 15, 2);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('inventories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('name');
            $table->decimal('quantity', 15, 2);
            $table->timestamps();
        });
        Schema::create('vouchers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('account')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('related_voucher')->nullable();
            $table->string('voucher_type')->default('Voucher');
            $table->string('voucher_status')->nullable();
            $table->unsignedInteger('invoice_id')->nullable();
            $table->unsignedInteger('receipt_id')->nullable();
            $table->unsignedInteger('payment_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('receipts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('invoice_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('account_masters', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('company_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('option');
            $table->string('value')->nullable();
            $table->timestamps();
        });
        Schema::create('recycle_bin_entries', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('resource_type', 20);
            $table->unsignedBigInteger('resource_id');
            $table->string('label')->nullable();
            $table->string('party')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->json('meta');
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at');
            $table->timestamps();
        });

        DB::table('account_masters')->insert(['id' => 5, 'name' => 'Arora Collection']);
        DB::table('inventories')->insert(['id' => 1, 'company_id' => 1, 'name' => 'Shirt', 'quantity' => 10]);
        DB::table('invoices')->insert(['id' => 1, 'company_id' => 1, 'invoice_number' => 'INV-0001', 'account_master_id' => 5, 'total' => 1200]);
        DB::table('invoice_items')->insert(['id' => 1, 'company_id' => 1, 'invoice_id' => 1, 'inventory_id' => 1, 'quantity' => 3]);
        DB::table('vouchers')->insert([
            ['id' => 1, 'company_id' => 1, 'account' => 'Sales', 'debit' => 0, 'credit' => 1200, 'related_voucher' => '1, 2', 'voucher_type' => 'Invoice', 'invoice_id' => 1],
            ['id' => 2, 'company_id' => 1, 'account' => 'Arora Collection', 'debit' => 1200, 'credit' => 0, 'related_voucher' => '1, 2', 'voucher_type' => 'Invoice', 'invoice_id' => 1],
            ['id' => 3, 'company_id' => 1, 'account' => 'Cash', 'debit' => 500, 'credit' => 0, 'related_voucher' => '3, 4', 'voucher_type' => 'Voucher', 'invoice_id' => null],
            ['id' => 4, 'company_id' => 1, 'account' => 'Rent', 'debit' => 0, 'credit' => 500, 'related_voucher' => '3, 4', 'voucher_type' => 'Voucher', 'invoice_id' => null],
            ['id' => 5, 'company_id' => 1, 'account' => 'Unlinked', 'debit' => 50, 'credit' => 0, 'related_voucher' => null, 'voucher_type' => 'Voucher', 'invoice_id' => null],
            ['id' => 6, 'company_id' => 1, 'account' => 'Also unlinked', 'debit' => 70, 'credit' => 0, 'related_voucher' => null, 'voucher_type' => 'Voucher', 'invoice_id' => null],
        ]);
    }

    public function test_deleted_invoice_is_hidden_and_returns_its_stock(): void
    {
        RecycleBin::trashInvoice(Invoice::find(1));

        $this->assertNull(Invoice::find(1));
        $this->assertNotNull(Invoice::withTrashed()->find(1)->deleted_at);
        $this->assertSame(0, InvoiceItem::where('invoice_id', 1)->count());
        $this->assertSame(0, Voucher::whereIn('id', [1, 2])->count());
        $this->assertEquals(13, DB::table('inventories')->where('id', 1)->value('quantity'));

        $entry = RecycleBinEntry::sole();
        $this->assertSame(['invoice', 'INV-0001', 'Arora Collection'], [$entry->resource_type, $entry->label, $entry->party]);
        $this->assertTrue($entry->purgeAt()->isSameDay(now()->addDays(RecycleBinEntry::RETENTION_DAYS)));
    }

    public function test_restoring_an_invoice_brings_back_its_rows_and_retakes_stock(): void
    {
        RecycleBin::trashInvoice(Invoice::find(1));
        RecycleBin::restore(RecycleBinEntry::sole());

        $this->assertNotNull(Invoice::find(1));
        $this->assertSame(1, InvoiceItem::where('invoice_id', 1)->count());
        $this->assertSame(2, Voucher::whereIn('id', [1, 2])->count());
        $this->assertEquals(10, DB::table('inventories')->where('id', 1)->value('quantity'));
        $this->assertSame(0, RecycleBinEntry::count());
    }

    public function test_restore_is_refused_when_stock_ran_out_and_nothing_is_half_restored(): void
    {
        DB::table('company_settings')->insert(['company_id' => 1, 'option' => 'allow_negative_inventory', 'value' => 'NO']);
        RecycleBin::trashInvoice(Invoice::find(1));
        DB::table('inventories')->where('id', 1)->update(['quantity' => 1]);

        try {
            RecycleBin::restore(RecycleBinEntry::sole());
            $this->fail('Restore should have been refused');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Not enough stock', $e->getMessage());
        }

        $this->assertNull(Invoice::find(1));
        $this->assertSame(0, Voucher::whereIn('id', [1, 2])->count());
        $this->assertEquals(1, DB::table('inventories')->where('id', 1)->value('quantity'));
        $this->assertSame(1, RecycleBinEntry::count());
    }

    public function test_voucher_delete_trashes_all_its_legs_and_restores_them(): void
    {
        RecycleBin::trashVoucher(Voucher::find(3));

        $this->assertSame(0, Voucher::whereIn('id', [3, 4])->count());
        $this->assertSame(2, Voucher::onlyTrashed()->whereIn('id', [3, 4])->count());

        RecycleBin::restore(RecycleBinEntry::sole());
        $this->assertSame(2, Voucher::whereIn('id', [3, 4])->count());
    }

    public function test_deleting_an_unlinked_voucher_leaves_other_unlinked_vouchers_alone(): void
    {
        RecycleBin::trashVoucher(Voucher::find(5));

        $this->assertNull(Voucher::find(5));
        $this->assertNotNull(Voucher::find(6));
    }

    public function test_purge_removes_records_older_than_the_retention_window_only(): void
    {
        RecycleBin::trashInvoice(Invoice::find(1));
        RecycleBin::trashVoucher(Voucher::find(3));
        RecycleBinEntry::where('resource_type', 'invoice')->update(['deleted_at' => now()->subDays(RecycleBinEntry::RETENTION_DAYS)->subMinute()]);

        Artisan::call('recycle-bin:purge');

        $this->assertNull(Invoice::withTrashed()->find(1));
        $this->assertSame(0, DB::table('invoice_items')->where('invoice_id', 1)->count());
        $this->assertSame(0, DB::table('vouchers')->whereIn('id', [1, 2])->count());
        // The voucher deleted just now is still restorable.
        $this->assertSame(2, Voucher::onlyTrashed()->whereIn('id', [3, 4])->count());
        $this->assertSame(['voucher'], RecycleBinEntry::pluck('resource_type')->all());
    }
}
