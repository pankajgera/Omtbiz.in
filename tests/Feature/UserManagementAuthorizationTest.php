<?php

namespace Tests\Feature;

use App\Http\Middleware\ConfigMiddleware;
use App\Models\User;
use Illuminate\Routing\Contracts\ControllerDispatcher as DispatcherContract;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;
use Tests\TestCase;

class UserManagementAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        // This matrix intentionally sends more than the normal per-minute quota.
        RateLimiter::for('api', fn () => Limit::perMinute(1000));
        $this->withoutMiddleware(ConfigMiddleware::class);

        // Exercise the actual route/auth/company/admin middleware pipeline, but
        // replace account writes with a marker so no database or media is changed.
        $dispatcher = Mockery::mock(ControllerDispatcher::class, [$this->app])->makePartial();
        $dispatcher->shouldReceive('dispatch')->andReturn(response()->json(['handler_reached' => true]));
        $this->app->instance(DispatcherContract::class, $dispatcher);
    }

    private function accountWrites(): array
    {
        return [
            ['POST', '/api/users', ['role' => 'admin']],
            ['PUT', '/api/users/42', ['role' => 'admin']], // own account
            ['PATCH', '/api/users/99', ['role' => 'admin']], // another account
            ['DELETE', '/api/users/42', []],
            ['POST', '/api/users/delete', ['id' => [42, 99]]],
            ['PUT', '/api/settings/profile', ['name' => 'Changed']],
            ['POST', '/api/settings/profile/upload-avatar', []],
            ['POST', '/api/customers', ['password' => 'test-password']],
            ['PUT', '/api/customers/42', ['name' => 'Changed']],
            ['PATCH', '/api/customers/99', ['password' => 'test-password']],
            ['DELETE', '/api/customers/42', []],
            ['POST', '/api/customers/delete', ['id' => [42, 99]]],
        ];
    }

    private function signInAsRole(string $role): void
    {
        $user = new User();
        $user->forceFill(['id' => 42, 'company_id' => 7, 'role' => $role]);
        $this->actingAs($user, 'api');
    }

    public function test_non_admins_cannot_reach_any_account_write_handler(): void
    {
        foreach (['accountant', 'estimate', 'dispatch', 'customer', 'Admin'] as $role) {
            $this->signInAsRole($role);
            foreach ($this->accountWrites() as [$method, $url, $payload]) {
                $this->json($method, $url, $payload, ['company' => '999'])
                    ->assertForbidden()
                    ->assertExactJson(['error' => 'admin_only']);
            }
        }
    }

    public function test_admins_can_reach_the_existing_account_write_handlers(): void
    {
        $this->signInAsRole('admin');
        foreach ($this->accountWrites() as [$method, $url, $payload]) {
            $this->json($method, $url, $payload)
                ->assertOk()
                ->assertExactJson(['handler_reached' => true]);
        }
    }

    public function test_anonymous_requests_cannot_reach_account_write_handlers(): void
    {
        foreach ($this->accountWrites() as [$method, $url, $payload]) {
            $this->json($method, $url, $payload)->assertUnauthorized();
        }
    }
}
