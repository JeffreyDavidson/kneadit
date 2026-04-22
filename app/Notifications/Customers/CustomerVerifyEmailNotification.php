<?php

namespace App\Notifications\Customers;

use App\Models\Customers\Customer;
use App\Services\Settings\TenantSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class CustomerVerifyEmailNotification extends Notification
{
    use Queueable;

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Customer $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'account.email.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );

        $storeName = app(TenantSettings::class)->store->name;

        return (new MailMessage)
            ->subject("Verify your email — {$storeName}")
            ->line("Please click the button below to verify your email address for {$storeName}.")
            ->action('Verify email', $url)
            ->line('If you did not create an account, no further action is required.');
    }
}
