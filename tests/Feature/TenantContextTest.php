<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureCompanyContext;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TenantContextTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('name');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('items');

        parent::tearDown();
    }

    public function test_client_company_header_is_overwritten_by_authenticated_company(): void
    {
        $user = new User(['company_id' => 7]);
        $request = Request::create('/api/items', 'GET');
        $request->headers->set('company', '99');
        $request->setUserResolver(fn () => $user);

        $response = app(EnsureCompanyContext::class)->handle($request, function (Request $request) {
            return response()->json([
                'header' => $request->header('company'),
                'attribute' => $request->attributes->get('company_id'),
            ]);
        });

        $this->assertSame('7', $response->getData(true)['header']);
        $this->assertSame(7, $response->getData(true)['attribute']);
    }

    public function test_missing_company_assignment_is_rejected(): void
    {
        $request = Request::create('/api/items', 'GET');
        $request->setUserResolver(fn () => new User());

        $response = app(EnsureCompanyContext::class)->handle($request, fn () => response('ok'));

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_tenant_scope_blocks_direct_cross_company_lookup(): void
    {
        Item::withoutGlobalScopes()->insert([
            ['id' => 1, 'company_id' => 7, 'name' => 'Own item'],
            ['id' => 2, 'company_id' => 99, 'name' => 'Foreign item'],
        ]);

        request()->attributes->set('company_id', 7);

        $this->assertSame(1, Item::query()->count());
        $this->assertNotNull(Item::find(1));
        $this->assertNull(Item::find(2));
    }

    public function test_business_routes_run_tenant_context_after_authentication(): void
    {
        $middleware = Route::getRoutes()->getByName('bootstrap')->gatherMiddleware();

        $this->assertContains('auth:api', $middleware);
        $this->assertContains('company.context', $middleware);
        $this->assertLessThan(
            array_search('company.context', $middleware, true),
            array_search('auth:api', $middleware, true)
        );
    }
}
