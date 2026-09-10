<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CrmAccountApproved extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Favala CRM account has been approved')
            ->greeting("Hi {$notifiable->name},")
            ->line('Good news — an administrator has approved your Favala CRM account.')
            ->line('You can now sign in using the email and password your administrator set up for you.')
            ->action('Sign in to Favala CRM', route('login'))
            ->line('If you were not expecting this account, please contact your administrator.');
    }
}
