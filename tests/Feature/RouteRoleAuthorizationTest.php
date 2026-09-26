<?php

namespace Tests\Feature;

use App\Http\Middleware\ConfigMiddleware;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Routing\Contracts\ControllerDispatcher as DispatcherContract;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;
use Tests\TestCase;

class RouteRoleAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        RateLimiter::for('api', fn () => Limit::none());
        $this->withoutMiddleware(ConfigMiddleware::class);
        // Real route/middleware pipeline; never execute business writes.
        $dispatcher = Mockery::mock(ControllerDispatcher::class, [$this->app])->makePartial();
        $dispatcher->shouldReceive('dispatch')->andReturnUsing(fn () => response()->json([
            'handler_reached' => true,
            'company' => request()->header('company'),
        ]));
        $this->app->instance(DispatcherContract::class, $dispatcher);
    }

    private function signIn(string $role): void
    {
        $this->actingAs((new User)->forceFill(['id' => 42, 'company_id' => 7, 'role' => $role]), 'api');
    }

    public function test_every_business_route_enforces_role_and_preserves_tenant_context(): void
    {
        // Binding resource IDs is business data access; replace it only for this
        // all-route handler matrix, just as controller writes are replaced.
        app('router')->substituteImplicitBindingsUsing(fn () => null);
        $count = 0;
        foreach (app('router')->getRoutes() as $route) {
            if (!in_array('company.context', $route->middleware(), true)) continue;
            $this->assertContains('route.role', $route->middleware(), $route->uri());
            $url = '/' . preg_replace('/\{[^}]+\}/', '42', $route->uri());
            foreach (array_diff($route->methods(), ['HEAD']) as $method) {
                foreach (['admin', 'accountant', 'estimate', 'dispatch', 'customer', 'employee', 'Admin', 'ADMIN', 'admin ', 'unknown', ''] as $role) {
                    $this->signIn($role);
                    $allowed = $this->expectedAccess($role, $method, $url);
                    $response = $this->json($method, $url, ['role' => 'admin', 'type' => 'estimate'], ['company' => '999']);
                    $this->assertSame($allowed ? 200 : 403, $response->status(), "$role $method $url: " . $response->getContent());
                    if ($allowed) $response->assertJson(['handler_reached' => true, 'company' => '7']);
                    $count++;
                }
            }
        }
        $this->assertGreaterThan(2000, $count, 'All business route methods must be exercised');
    }

    // Independent URL policy derived from the supported screens and workflows.
    private function expectedAccess(string $role, string $method, string $url): bool
    {
        if ($role === 'admin') return true;
        if (!in_array($role, ['accountant', 'estimate', 'dispatch'], true)) return false;
        if (in_array($url, ['/api/bootstrap', '/api/settings/profile', '/api/settings/notifications'], true) && $method === 'GET') return true;
        if ($role === 'estimate') {
            return str_starts_with($url, '/api/estimates')
                || ($method === 'GET' && in_array($url, ['/api/inventory', '/api/customers', '/api/customers/42'], true))
                || ($method === 'POST' && $url === '/api/public-shares');
        }
        if ($role === 'dispatch') return str_starts_with($url, '/api/dispatch');
        if (preg_match('#^/api/(users|audit-logs)(/|$)#', $url)) return false;
        if (str_starts_with($url, '/api/settings/')) return $method === 'GET' && in_array($url, ['/api/settings/colors', '/api/settings/get-inventory-type', '/api/settings/get-customize-setting'], true);
        if (str_starts_with($url, '/api/categories')) return $method === 'GET' && $url === '/api/categories';
        if (str_starts_with($url, '/api/states')) return $method === 'GET' && in_array($url, ['/api/states', '/api/states/42'], true);
        if (str_starts_with($url, '/api/customers')) return $method === 'GET' && in_array($url, ['/api/customers', '/api/customers/42'], true);
        if (preg_match('#^/api/(invoices|payments|vouchers)(/|$)#', $url)) {
            if ($method === 'GET' && $url === '/api/vouchers/42/edit') return true; // Accountant read-only view.
            if (in_array($method, ['PUT', 'PATCH', 'DELETE'], true) || preg_match('#/(edit|delete|update|approve|decline|approve-multiple|decline-multiple)$#', $url)) return false;
        }
        if (str_starts_with($url, '/api/receipts') && ($method === 'DELETE' || preg_match('#/(delete|approve|decline|approve-multiple|decline-multiple)$#', $url))) return false;
        return true;
    }

    public function test_guests_cannot_reach_any_business_route(): void
    {
        foreach (app('router')->getRoutes() as $route) {
            if (!in_array('company.context', $route->middleware(), true)) continue;
            $url = '/' . preg_replace('/\{[^}]+\}/', '42', $route->uri());
            foreach (array_diff($route->methods(), ['HEAD']) as $method) $this->json($method, $url)->assertUnauthorized();
        }
    }

    public function test_a_changed_database_role_takes_effect_on_the_next_request(): void
    {
        $this->signIn('admin');
        $this->getJson('/api/users')->assertOk();
        auth('api')->user()->role = 'accountant';
        $this->getJson('/api/users')->assertForbidden();
        $this->getJson('/api/invoices')->assertOk();
    }

    public function test_role_denial_precedes_implicit_database_binding(): void
    {
        $this->signIn('dispatch');
        // No estimates table exists: a binding query before authorization would fail.
        $this->getJson('/api/invoices/estimate/42')->assertForbidden();
    }
}
