<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\MailResetPasswordNotification;
use Illuminate\Http\Request;
use RuntimeException;
use Tests\TestCase;

class PasswordResetTrustedHostTest extends TestCase
{
    public function test_hostile_request_host_cannot_change_password_reset_link(): void
    {
        config(['app.url' => 'https://trusted.example/app']);
        $this->app->instance('request', Request::create(
            'https://attacker.example/api/auth/password/email',
            'POST',
            [],
            [],
            [],
            ['HTTP_HOST' => 'attacker.example']
        ));

        $message = (new MailResetPasswordNotification('token/with symbols'))->toMail(new User());

        $this->assertSame(
            'https://trusted.example/app/reset-password/token%2Fwith%20symbols',
            $message->actionUrl
        );
        $this->assertStringNotContainsString('attacker.example', $message->actionUrl);
    }

    public function test_invalid_app_url_stops_reset_email_generation(): void
    {
        config(['app.url' => 'attacker.example']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_URL');

        (new MailResetPasswordNotification('token'))->toMail(new User());
    }
}
