<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReportDesignTest extends TestCase
{
    public static function reportData(int $rowCount = 1): array
    {
        $document = (object) [
            'invoice_number' => 'EST2026-0003-006471', 'invoice_date' => '2026-09-27 13:32:21',
            'estimate_number' => 'EST2026-0003-006471', 'estimate_date' => '2026-09-27 13:32:21',
            'receipt_number' => 'REC-006471', 'receipt_date' => '2026-09-27', 'receipt_mode' => 'Cash', 'amount' => 4000,
            'reference_number' => 'REF2026-0003-006471', 'master' => (object) ['name' => 'PRAVESH'],
            'sub_total' => 4000 * $rowCount, 'total' => 4030 * $rowCount, 'notes' => 'ss',
            'indirect_income' => 'Packing', 'indirect_income_value' => 50 * $rowCount,
            'indirect_expense' => 'Discount', 'indirect_expense_value' => 20 * $rowCount,
        ];
        $items = collect(range(1, $rowCount))->map(fn ($i) => (object) [
            'name' => $rowCount === 1 ? '5738' : "Item {$i} with a longer description that wraps naturally",
            'quantity' => 8, 'sale_price' => 500, 'total' => 4000, 'total_amount' => 400000,
            'inventory' => (object) ['unit' => 'pcs'],
        ]);

        return [
            'invoice' => clone $document, 'estimate' => clone $document, 'receipt' => clone $document,
            'invoice_items' => $items, 'estimate_items' => $items, 'items' => $items,
            'total_quantity' => 8 * $rowCount, 'total_amount' => 4030 * $rowCount,
            'company' => (object) ['name' => 'Omtbiz'], 'from_date' => '01/09/2026', 'to_date' => '27/09/2026',
            'totalAmount' => 400000 * $rowCount, 'income' => 500000, 'totalExpense' => 100000,
            'expenseCategories' => collect([(object) ['category' => (object) ['name' => 'Office supplies'], 'total_amount' => 100000]]),
            'opening_balance' => 5000, 'master_type' => 'Cr', 'credit_debit_sum' => 4000, 'credit_debit_type' => 'Cr',
            'related_vouchers' => [[['id' => 1, 'date' => '2026-09-27', 'account' => 'Office supplies', 'debit' => 1000, 'credit' => 0]]],
            'printPreview' => false,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id(); $table->integer('company_id'); $table->string('option'); $table->string('value');
        });
        Schema::create('currencies', function (Blueprint $table) {
            $table->id(); $table->integer('precision'); $table->string('symbol');
            $table->string('decimal_separator'); $table->string('thousand_separator'); $table->boolean('swap_currency_symbol');
        });
        DB::table('company_settings')->insert(['company_id' => 1, 'option' => 'currency', 'value' => '1']);
        DB::table('currencies')->insert(['id' => 1, 'precision' => 2, 'symbol' => '₹', 'decimal_separator' => '.', 'thousand_separator' => ',', 'swap_currency_symbol' => false]);
    }

    public function test_pdf_renderer_defaults_to_a4_portrait_without_template_page_rules(): void
    {
        $renderer = app('dompdf.wrapper')->loadHTML('<html><body>A4 default</body></html>');
        $renderer->output();
        $canvas = $renderer->getDomPDF()->getCanvas();

        $this->assertEqualsWithDelta(595.28, $canvas->get_width(), 0.02);
        $this->assertEqualsWithDelta(841.89, $canvas->get_height(), 0.02);
    }

    public function test_all_matching_report_templates_render_as_pdf_with_their_existing_content(): void
    {
        $views = ['reports.invoice', 'invoice.invoice1', 'estimate.estimate1', 'receipt.receipt', 'reports.banks', 'reports.expenses', 'reports.profit-loss', 'reports.sales-items'];
        foreach ($views as $view) {
            $html = view('app.pdf.' . $view, self::reportData())->render();
            $this->assertStringContainsString('#203746', $html, $view);
            $this->assertStringNotContainsString('Invoice print controls', $html, $view);
            // The template must select A4 even if the server default is Letter.
            $renderer = app('dompdf.wrapper')->setPaper('letter')->loadHTML($html);
            $pdf = $renderer->output();
            $canvas = $renderer->getDomPDF()->getCanvas();
            $this->assertEqualsWithDelta(595.28, $canvas->get_width(), 0.02, $view);
            $this->assertEqualsWithDelta(841.89, $canvas->get_height(), 0.02, $view);
            $this->assertStringStartsWith('%PDF-', $pdf, $view);
            if ($directory = getenv('REPORT_DESIGN_QA_DIR')) {
                file_put_contents($directory . '/' . $view . '.html', $html);
                file_put_contents($directory . '/' . $view . '.pdf', $pdf);
            }
        }
    }

    public function test_long_invoices_paginate_without_losing_totals_or_footer(): void
    {
        $html = view('app.pdf.reports.invoice', self::reportData(65))->render();
        $this->assertStringContainsString('Item 65 with a longer description', $html);
        $this->assertStringContainsString('Packing', $html);
        $this->assertStringContainsString('Discount', $html);
        $this->assertStringContainsString('₹ 261950', $html);
        $this->assertStringContainsString('Authorised Signatory', $html);
        $pdf = app('dompdf.wrapper')->loadHTML($html);
        $bytes = $pdf->output();
        $this->assertGreaterThan(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
        if ($directory = getenv('REPORT_DESIGN_QA_DIR')) {
            file_put_contents($directory . '/long-invoice.pdf', $bytes);
        }
    }

    public function test_document_footer_is_once_at_the_bottom_of_the_last_pdf_page(): void
    {
        foreach ([1, 14, 40, 65] as $rowCount) {
            $renderer = app('dompdf.wrapper')->loadView('app.pdf.reports.invoice', self::reportData($rowCount));
            $dompdf = $renderer->getDomPDF();
            $callbacks = [];
            foreach ($dompdf->getCallbacks() as $event => $handlers) {
                foreach ($handlers as $handler) {
                    $callbacks[] = ['event' => $event, 'f' => $handler];
                }
            }
            $footers = [];
            $tables = [];
            $callbacks[] = ['event' => 'end_frame', 'f' => function ($frame, $canvas) use (&$footers, &$tables): void {
                $node = $frame->get_node();
                if (! $node instanceof \DOMElement) {
                    return;
                }
                $position = [
                    'page' => $canvas->get_page_number(),
                    'top' => $frame->get_position('y'),
                    'bottom' => $frame->get_position('y') + $frame->get_margin_height(),
                ];
                if ($node->hasAttribute('data-last-page-footer')) {
                    $footers[] = $position;
                } elseif ($node->getAttribute('class') === 'line-items') {
                    $tables[] = $position;
                }
            }];
            $dompdf->setCallbacks($callbacks);
            $renderer->output();

            $this->assertCount(1, $footers, "{$rowCount} rows");
            $canvas = $dompdf->getCanvas();
            $this->assertSame($canvas->get_page_count(), $footers[0]['page']);
            $this->assertEqualsWithDelta($canvas->get_height() - 12 * 72 / 25.4, $footers[0]['bottom'], 0.1);
            foreach ($tables as $table) {
                if ($table['page'] === $footers[0]['page']) {
                    $this->assertGreaterThanOrEqual($table['bottom'], $footers[0]['top']);
                }
            }
        }
    }

    public function test_remaining_pdf_templates_use_a4_portrait_including_customer_ledger(): void
    {
        $data = self::reportData();
        $user = (object) [
            'company' => $data['company'], 'billingaddress' => null, 'shippingaddress' => null,
            'currency' => \App\Models\Currency::findOrFail(1),
        ];
        $line = (object) ['name' => 'Paper size check', 'description' => 'A4 item', 'quantity' => 2, 'price' => 200000, 'total' => 400000];
        foreach (['invoice', 'estimate'] as $kind) {
            $data[$kind]->user = $user;
            $data[$kind]->inventories = collect([$line]);
            $data[$kind]->items = collect([$line]);
            $data[$kind]->formattedInvoiceDate = '27/09/2026';
            $data[$kind]->formattedEstimateDate = '27/09/2026';
            $data[$kind]->formattedDueDate = '30/09/2026';
            $data[$kind]->formattedExpiryDate = '30/09/2026';
            $data[$kind]->total = 400000;
            $data[$kind]->sub_total = 400000;
        }
        $data += [
            'logo' => null, 'company_address' => null,
            'party_name' => 'PRAVESH', 'invoice_number' => 'EST2026-0003-006471', 'reference_number' => '006471',
            'customers' => collect([(object) ['name' => 'PRAVESH', 'invoices' => collect([$data['invoice']]), 'totalAmount' => 400000]]),
            'ledger' => (object) ['account' => 'PRAVESH', 'accountMaster' => (object) ['groups' => 'Customers']],
            'inventory_sum' => 2, 'total_opening_balance_cr' => 100, 'total_opening_balance_dr' => 0,
            'current_balance_cr' => 4000, 'current_balance_dr' => 0, 'closing_balance_cr' => 4100, 'closing_balance_dr' => 0,
        ];
        $data['related_vouchers'] = collect([(object) [
            'id' => 1, 'invoice_id' => 1, 'receipt_id' => null, 'invoice' => $data['invoice'],
            'date' => '2026-09-27', 'account' => 'PRAVESH', 'credit' => 4000, 'debit' => 0,
        ]]);

        foreach (['invoice.invoice2', 'invoice.invoice3', 'estimate.estimate2', 'estimate.estimate3', 'reports.sales-customers', 'reports.customers', 'reports.slip'] as $view) {
            $renderer = app('dompdf.wrapper')->setPaper('letter')->loadView('app.pdf.' . $view, $data);
            $bytes = $renderer->output();
            $canvas = $renderer->getDomPDF()->getCanvas();
            $this->assertEqualsWithDelta(595.28, $canvas->get_width(), 0.02, $view);
            $this->assertEqualsWithDelta(841.89, $canvas->get_height(), 0.02, $view);
            if ($directory = getenv('REPORT_DESIGN_QA_DIR')) {
                file_put_contents($directory . '/' . $view . '.pdf', $bytes);
            }
        }
    }
}
