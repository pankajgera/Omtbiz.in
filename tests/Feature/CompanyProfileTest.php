<?php

namespace Tests\Feature;

use App\Http\Controllers\CompanyController;
use App\Models\User;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class CompanyProfileTest extends TestCase
{
    public function test_profile_uses_the_authenticated_user(): void
    {
        $user = new User([
            'name' => 'Current User',
            'email' => 'current@example.com',
        ]);
        $user->id = 42;

        $this->actingAs($user, 'web');

        $profile = app(CompanyController::class)->getAdmin();

        $this->assertSame($user, $profile);
    }

    public function test_company_profile_uses_the_authenticated_user(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 42;
        $user->name = 'Current User';
        $user->setAppends([]);
        $user->shouldReceive('load')
            ->once()
            ->with(['addresses', 'addresses.country', 'company'])
            ->andReturnSelf();

        $request = Request::create('/api/settings/company');
        $request->setUserResolver(fn () => $user);

        $response = app(CompanyController::class)->getAdminCompany($request);

        $this->assertSame(42, $response->getData(true)['user']['id']);
    }
}
