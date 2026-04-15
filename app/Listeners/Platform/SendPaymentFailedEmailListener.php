<?php

namespace App\Listeners\Platform;

use App\Events\Platform\PaymentFailed;
use App\Listeners\SendEmailListener;
use App\Mail\Platform\PaymentFailedMail;
use Illuminate\Contracts\Mail\Mailable;

class SendPaymentFailedEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var PaymentFailed $event */
        return $event->user->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var PaymentFailed $event */
        return new PaymentFailedMail($event->user);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var PaymentFailed $event */
        return ['user' => $event->user->email];
    }
}
