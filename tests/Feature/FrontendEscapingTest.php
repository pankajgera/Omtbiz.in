<?php

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class FrontendEscapingTest extends TestCase
{
    public function test_generic_table_cells_render_values_as_text(): void
    {
        $component = file_get_contents(base_path('resources/js/components/base/base-table/components/TableCell.js'));

        $this->assertStringNotContainsString('innerHTML', $component);
        $this->assertStringContainsString('String(value)', $component);
    }

    public function test_database_influenced_money_and_quantity_values_do_not_use_v_html(): void
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(base_path('resources/js/views'))
        );

        foreach ($files as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'vue') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            $this->assertDoesNotMatchRegularExpression(
                '/v-html="[^"]*(?:formatMoney|totalQuantity|due_amount)/',
                $contents,
                $file->getPathname()
            );
        }
    }

    public function test_money_formatter_no_longer_constructs_html(): void
    {
        $utility = file_get_contents(base_path('resources/js/helpers/utilities.js'));

        $this->assertStringNotContainsString('<span style="font-family: sans-serif">', $utility);
        $this->assertStringContainsString('let moneySymbol = String(symbol)', $utility);
    }
}
