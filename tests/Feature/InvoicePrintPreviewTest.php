<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InvoicePrintPreviewTest extends TestCase
{
    public function test_preview_renders_saved_details_and_controls_but_pdf_has_no_toolbar(): void
    {
        $invoice = (object) [
            'invoice_number' => 'INV-100', 'invoice_date' => '2026-09-27',
            'reference_number' => 'REF-2026-100', 'master' => (object) ['name' => 'Preview party'],
            'sub_total' => 4000, 'total' => 4000, 'notes' => '<script>bad()</script>',
            'indirect_income' => null, 'indirect_expense' => null,
        ];
        $data = [
            'invoice' => $invoice, 'total_quantity' => 8, 'total_amount' => 4000,
            'invoice_items' => [(object) [
                'name' => 'Preview item', 'quantity' => 8, 'sale_price' => 500,
                'total' => 4000, 'inventory' => null,
            ]],
        ];

        $preview = view('app.pdf.reports.invoice', $data + ['printPreview' => true])->render();
        $this->assertStringContainsString('Invoice print controls', $preview);
        $this->assertStringContainsString('?download=1', $preview);
        $this->assertStringContainsString('Preview party', $preview);
        $this->assertStringContainsString('Preview item', $preview);
        $this->assertMatchesRegularExpression('/FOUR THOUSAND\s+ONLY/', $preview);
        $this->assertStringNotContainsString('<script>bad()</script>', $preview);

        $pdf = view('app.pdf.reports.invoice', $data + ['printPreview' => false])->render();
        $this->assertStringNotContainsString('Invoice print controls', $pdf);
        $this->assertStringContainsString('Preview item', $pdf);
    }

    public function test_preview_and_download_require_an_active_invoice_share(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('public_shares', function (Blueprint $table): void {
            $table->id();
            $table->string('token');
            $table->string('resource_type');
            $table->timestamp('revoked_at')->nullable();
        });
        DB::table('public_shares')->insert([
            ['token' => 'revoked-token', 'resource_type' => 'invoice', 'revoked_at' => now()],
            ['token' => 'wrong-type-token', 'resource_type' => 'receipt', 'revoked_at' => null],
        ]);

        foreach (['preview', 'download'] as $mode) {
            foreach (['unknown-token', 'revoked-token', 'wrong-type-token'] as $token) {
                $this->get("/reports/invoice/{$token}?{$mode}=1")->assertNotFound();
            }
        }
    }
}
