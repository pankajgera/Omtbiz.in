<?php

namespace Tests\Feature;

use App\Support\InitialAdminCredentials;
use RuntimeException;
use Tests\TestCase;

class InitialAdminCredentialsTest extends TestCase
{
    public function test_installation_requires_a_valid_strong_initial_admin_identity(): void
    {
        config([
            'initial-admin.name' => 'Production Administrator',
            'initial-admin.email' => 'admin@example.test',
            'initial-admin.password' => 'Long-Unique-Secret-2026!',
        ]);

        $this->assertSame([
            'name' => 'Production Administrator',
            'email' => 'admin@example.test',
            'password' => 'Long-Unique-Secret-2026!',
        ], InitialAdminCredentials::fromConfig());
    }

    public function test_installation_rejects_missing_or_invalid_email(): void
    {
        config([
            'initial-admin.email' => 'not-an-email',
            'initial-admin.password' => 'Long-Unique-Secret-2026!',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('INITIAL_ADMIN_EMAIL');

        InitialAdminCredentials::fromConfig();
    }

    public function test_installation_rejects_weak_password(): void
    {
        config([
            'initial-admin.email' => 'admin@example.test',
            'initial-admin.password' => 'password',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('INITIAL_ADMIN_PASSWORD');

        InitialAdminCredentials::fromConfig();
    }

    public function test_seeders_do_not_contain_the_previous_fixed_credentials(): void
    {
        $seeders = file_get_contents(database_path('seeds/UsersTableSeeder.php'))
            . file_get_contents(database_path('seeds/CompanySeeder.php'));

        $this->assertStringNotContainsString('testing@gmail.com', $seeders);
        $this->assertStringNotContainsString('testing@123', $seeders);
    }
}
