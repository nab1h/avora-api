<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Invitation $invitation
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = config('app.frontend_url')
            . '/accept-invitation/'
            . $this->invitation->token;

        return (new MailMessage)
            ->subject('You have been invited to Avora')
            ->greeting('Hello!')
            ->line('You have been invited to join Avora.')
            ->line('Click the button below to accept your invitation and create your account.')
            ->action('Accept Invitation', $url)
            ->line('This invitation will expire in 24 hours.');
    }
}