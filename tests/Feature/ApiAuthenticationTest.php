<?php

namespace Tests\Feature;

use App\Http\Middleware\AdminMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    public function test_business_api_requires_passport_authentication(): void
    {
        $route = Route::getRoutes()->getByName('bootstrap');

        $this->assertNotNull($route);
        $this->assertContains('auth:api', $route->gatherMiddleware());
    }

    public function test_public_health_endpoint_remains_available(): void
    {
        $route = Route::getRoutes()->getByName('ping');

        $this->assertNotNull($route);
        $this->assertNotContains('auth:api', $route->gatherMiddleware());
    }

    public function test_sensitive_routes_require_admin_middleware(): void
    {
        foreach ([
            'admin.data.delete',
            'upload.admin.company.logo',
            'admin.company',
            'admin.company.setting',
            'admin.environment.mail.save',
            'users.index',
            'audit-logs.index',
        ] as $routeName) {
            $route = Route::getRoutes()->getByName($routeName);

            $this->assertNotNull($route, "Missing route [{$routeName}].");
            $this->assertContains('admin:api', $route->gatherMiddleware());
        }
    }

    public function test_admin_middleware_rejects_non_admin_users(): void
    {
        $user = new User(['role' => 'accountant']);
        Auth::shouldReceive('guard')->with('api')->andReturnSelf();
        Auth::shouldReceive('user')->andReturn($user);

        $response = app(AdminMiddleware::class)->handle(
            Request::create('/api/settings/company', 'POST'),
            fn () => response()->json(['success' => true]),
            'api'
        );

        $this->assertSame(403, $response->getStatusCode());
    }
}
