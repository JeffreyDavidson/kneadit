<?php

namespace App\Listeners\Platform;

use App\Events\Platform\PaymentFailed;
use App\Listeners\SendEmailListener;
use App\Mail\Platform\PaymentFailedAlertMail;
use App\Support\DatabaseValue;
use Illuminate\Contracts\Mail\Mailable;

class SendPaymentFailedAlertListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        return DatabaseValue::nullableString(config('mail.platform_notify'));
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
