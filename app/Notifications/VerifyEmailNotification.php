<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $hash = sha1($notifiable->getEmailForVerification());

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => $hash,
            ]
        );

        $query = parse_url($verificationUrl, PHP_URL_QUERY);

        $url = config('app.frontend_url')
            .'/verify-email/'
            .$notifiable->getKey()
            .'/'
            .$hash
            .'?'
            .$query;

        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email', $url)
            ->line('This verification link will expire in 60 minutes.');
    }
}
