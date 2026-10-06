<?php

namespace App\Notifications\Platform;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * The email a new bakery owner gets to confirm their address before creating a
 * bakery. It is sent from the central site, so it only uses KneadIt branding
 * and never reads bakery settings.
 */
class OwnerVerifyEmailNotification extends VerifyEmail
{
    #[\Override]
    protected function buildMailMessage(mixed $url): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify your KneadIt email')
            ->line('Welcome to KneadIt! Please confirm your email address to set up your bakery.')
            ->action('Verify email', $url)
            ->line('If you did not create a KneadIt account, no further action is required.');
    }
}
