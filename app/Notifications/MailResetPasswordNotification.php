<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use Illuminate\Auth\Notifications\ResetPassword;
use RuntimeException;

class MailResetPasswordNotification extends ResetPassword
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        parent::__construct($token);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $trustedBaseUrl = rtrim((string) config('app.url'), '/');
        $scheme = parse_url($trustedBaseUrl, PHP_URL_SCHEME);
        $host = parse_url($trustedBaseUrl, PHP_URL_HOST);

        if (! in_array($scheme, ['http', 'https'], true) || ! is_string($host) || $host === '') {
            throw new RuntimeException('APP_URL must be a trusted absolute HTTP or HTTPS URL before password resets can be sent.');
        }

        $link = $trustedBaseUrl . '/reset-password/' . rawurlencode((string) $this->token);

        return ( new MailMessage )
            ->subject('Reset Password Notification')
            ->line("Hello! You are receiving this email because we received a password reset request for your account." )
            ->action('Reset Password', $link )
            ->line("This password reset link will expire in ".config('auth.passwords.users.expire')." minutes" )
            ->line("If you did not request a password reset, no further action is required." );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
