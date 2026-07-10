<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail(mixed $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject(__('verification.email_subject'))
            ->line(__('verification.email_line_1'))
            ->action(__('verification.email_action'), $verificationUrl)
            ->line(__('verification.email_line_2'))
            ->line(__('verification.email_line_3'));
    }
}
