<?php

namespace Tests\Feature;

use App\Http\Controllers\CompanyController;
use App\Jobs\EraseData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DataDeletionTest extends TestCase
{
    public function test_admin_can_queue_data_deletion_with_exact_confirmation(): void
    {
        Bus::fake();

        $response = app(CompanyController::class)->delete(
            $this->requestFor('admin', 'DELETE ALL DATA')
        );

        $this->assertSame(200, $response->getStatusCode());
        Bus::assertDispatched(EraseData::class);
    }

    public function test_non_admin_cannot_queue_data_deletion(): void
    {
        Bus::fake();

        $response = app(CompanyController::class)->delete(
            $this->requestFor('accountant', 'DELETE ALL DATA')
        );

        $this->assertSame(403, $response->getStatusCode());
        Bus::assertNotDispatched(EraseData::class);
    }

    public function test_incorrect_confirmation_does_not_queue_data_deletion(): void
    {
        Bus::fake();

        try {
            app(CompanyController::class)->delete(
                $this->requestFor('admin', 'delete all data')
            );
            $this->fail('Expected the confirmation validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('confirmation', $exception->errors());
        }

        Bus::assertNotDispatched(EraseData::class);
    }

    private function requestFor(string $role, string $confirmation): Request
    {
        $user = new User([
            'name' => 'Deletion Test User',
            'email' => 'delete-test@example.com',
            'role' => $role,
        ]);

        $request = Request::create('/api/settings/data/delete', 'DELETE', [
            'confirmation' => $confirmation,
        ]);
        $request->setUserResolver(fn () => $user);

        return $request;
    }
}
