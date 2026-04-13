<?php

namespace App\Listeners\Platform;

use App\Events\Platform\PaymentFailed;
use App\Listeners\SendEmailListener;
use App\Mail\Platform\HealthAlertMail;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Number;

class SendPaymentFailedAlertListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        return config('mail.platform_notify');
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var PaymentFailed $event */
        $message = "Payment failed for {$event->user->name} ({$event->user->email})"
            . ($event->tenant ? " — Tenant: {$event->tenant->store_name} ({$event->tenant->id})" : '')
            . "\nAmount: " . Number::currency($event->amount);

        return new HealthAlertMail($message);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var PaymentFailed $event */
        return ['user' => $event->user->email];
    }
}
