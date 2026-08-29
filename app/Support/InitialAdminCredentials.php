<?php

namespace App\Support;

use RuntimeException;

class InitialAdminCredentials
{
    public static function fromConfig(): array
    {
        $name = trim((string) config('initial-admin.name'));
        $email = trim((string) config('initial-admin.email'));
        $password = (string) config('initial-admin.password');

        if ($name === '') {
            throw new RuntimeException('INITIAL_ADMIN_NAME must not be empty.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('INITIAL_ADMIN_EMAIL must be a valid email address.');
        }

        if (strlen($password) < 16
            || ! preg_match('/[a-z]/', $password)
            || ! preg_match('/[A-Z]/', $password)
            || ! preg_match('/[0-9]/', $password)
            || ! preg_match('/[^a-zA-Z0-9]/', $password)) {
            throw new RuntimeException(
                'INITIAL_ADMIN_PASSWORD must contain at least 16 characters, including uppercase, lowercase, number, and symbol.'
            );
        }

        return compact('name', 'email', 'password');
    }
}
