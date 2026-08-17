<?php

namespace App\Listeners\Platform;

use App\Events\Platform\PaymentFailed;
use App\Listeners\SendEmailListener;
use App\Mail\Platform\PaymentFailedAlertMail;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Config;

class SendPaymentFailedAlertListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        $recipient = Config::get('mail.platform_notify');

        return is_string($recipient) ? $recipient : null;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var PaymentFailed $event */
        return new PaymentFailedAlertMail($event->user, $event->tenant, $event->amount);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var PaymentFailed $event */
        return ['user' => $event->user->email];
    }
}
