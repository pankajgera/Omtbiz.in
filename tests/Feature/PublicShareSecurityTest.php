<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Receipt;
use App\Support\PublicShareService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicShareSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        request()->attributes->remove('company_id');

        Schema::create('public_shares', function (Blueprint $table): void {
            $table->id();
            $table->string('token')->unique();
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('created_by')->nullable();
            $table->string('resource_type');
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->json('parameters')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('public_shares');

        parent::tearDown();
    }

    public function test_existing_random_invoice_link_is_preserved(): void
    {
        $invoice = new Invoice([
            'company_id' => 10,
            'unique_hash' => 'legacy-safe-token-with-sufficient-entropy',
        ]);
        $invoice->id = 101;
        $invoice->exists = true;

        $share = app(PublicShareService::class)->document($invoice, 'invoice', 5);

        $this->assertSame($invoice->unique_hash, $share->token);
        $this->assertSame(10, $share->company_id);
        $this->assertStringContainsString('/invoices/pdf/' . $invoice->unique_hash, app(PublicShareService::class)->url($share));
    }

    public function test_receipt_link_is_opaque_and_not_its_numeric_id(): void
    {
        $receipt = new Receipt(['company_id' => 10]);
        $receipt->id = 202;
        $receipt->exists = true;

        $share = app(PublicShareService::class)->document($receipt, 'receipt', 5);

        $this->assertNotSame((string) $receipt->id, $share->token);
        $this->assertSame(64, strlen($share->token));
        $this->assertStringContainsString('/receipts/pdf/' . $share->token, app(PublicShareService::class)->url($share));
    }

    public function test_report_share_binds_company_type_and_parameters(): void
    {
        $parameters = ['from_date' => '01/08/2026', 'to_date' => '24/08/2026'];
        $share = app(PublicShareService::class)->report(10, 5, 'expenses', $parameters);

        $this->assertSame(10, $share->company_id);
        $this->assertSame('report:expenses', $share->resource_type);
        $this->assertSame($parameters, $share->parameters);
        $this->assertStringContainsString('/reports/expenses/' . $share->token, app(PublicShareService::class)->url($share));
    }

    public function test_revoked_token_is_not_active(): void
    {
        $receipt = new Receipt(['company_id' => 10]);
        $receipt->id = 303;
        $receipt->exists = true;
        $share = app(PublicShareService::class)->document($receipt, 'receipt', 5);
        $share->update(['revoked_at' => now()]);

        $this->assertFalse(
            $share->newQueryWithoutScopes()->active()->whereKey($share->id)->exists()
        );
    }

    public function test_public_controllers_no_longer_resolve_company_hashes_or_numeric_receipts(): void
    {
        $reportController = file_get_contents(app_path('Http/Controllers/ReportController.php'));
        $frontendController = file_get_contents(app_path('Http/Controllers/FrontendController.php'));

        $this->assertStringNotContainsString("Company::where('unique_hash'", $reportController);
        $this->assertStringContainsString("sharedResource(\$id, 'receipt'", $frontendController);
    }
}
