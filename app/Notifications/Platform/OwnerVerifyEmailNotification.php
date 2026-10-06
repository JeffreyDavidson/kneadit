<?php

namespace App\Notifications\Platform;

use App\Models\Staff\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;

/**
 * The email a new bakery owner gets to confirm their address before creating a
 * bakery. It is sent from the central site in the shared KneadIt email layout
 * (`emails.platform.layout`), so it never reads bakery settings.
 */
class OwnerVerifyEmailNotification extends VerifyEmail
{
    /**
     * @param  User  $notifiable
     */
    #[\Override]
    public function toMail($notifiable): MailMessage
    {
        return new MailMessage()
            ->subject('Verify your KneadIt email')
            ->view(['emails.platform.verify-email', 'emails.platform.verify-email-text'], [
                'ownerName' => $notifiable->name,
                'verificationUrl' => $this->verificationUrl($notifiable),
                'expiresInMinutes' => Config::integer('auth.verification.expire', 60),
            ]);
    }
}
