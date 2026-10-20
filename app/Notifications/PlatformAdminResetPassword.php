<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlatformAdminResetPassword extends Notification
{
    public function __construct(
        private readonly string $token
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = rtrim(config('app.url'), '/')
            . route(
                'platform-admin.password.reset',
                ['token' => $this->token],
                false
            )
            . '?'
            . http_build_query([
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

        $minutes = (int) config(
            'auth.passwords.platform_admins.expire',
            30
        );

        return (new MailMessage)
            ->subject('Reset your Clinicora platform admin password')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line(
                'We received a password reset request for your '
                . 'Clinicora platform administrator account.'
            )
            ->action('Reset admin password', $url)
            ->line("This link expires in {$minutes} minutes.")
            ->line(
                'If you did not request this change, '
                . 'you can ignore this email.'
            );
    }
}