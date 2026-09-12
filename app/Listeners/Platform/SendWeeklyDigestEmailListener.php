<?php

namespace App\Listeners\Platform;

use App\Events\Platform\WeeklyDigestRequested;
use App\Listeners\SendEmailListener;
use App\Mail\Platform\WeeklyDigestMail;
use Illuminate\Contracts\Mail\Mailable;

class SendWeeklyDigestEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var WeeklyDigestRequested $event */
        return $event->user->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var WeeklyDigestRequested $event */
        return new WeeklyDigestMail(
            stats: $event->data->stats,
            topProducts: $event->data->topProducts,
            atRiskCustomers: $event->data->atRiskCustomers,
            upcomingCount: $event->data->upcomingCount,
            storeName: $event->data->storeName,
            adminUrl: $event->data->adminUrl,
        );
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var WeeklyDigestRequested $event */
        return ['user' => $event->user->email];
    }
}
