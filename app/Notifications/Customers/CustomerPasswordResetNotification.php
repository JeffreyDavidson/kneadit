<?php

namespace App\Notifications\Customers;

use App\Models\Customers\Customer;
use App\Services\Settings\TenantSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class CustomerPasswordResetNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly string $token) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        if (! $notifiable instanceof Customer) {
            throw new \InvalidArgumentException('Password reset notifications require a customer.');
        }

        $url = route('account.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $storeName = resolve(TenantSettings::class)->store->name;

        return (new MailMessage)
            ->subject("Reset your password — {$storeName}")
            ->line("You are receiving this email because a password reset was requested for your {$storeName} account.")
            ->action('Reset password', $url)
            ->line('This link will expire in ' . Config::integer('auth.passwords.customers.expire', 60) . ' minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
