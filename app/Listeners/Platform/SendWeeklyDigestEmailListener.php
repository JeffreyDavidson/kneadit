<?php

namespace App\Listeners\Platform;

use App\Events\Platform\WeeklyDigestRequested;
use App\Listeners\QueuedListener;
use App\Mail\Platform\WeeklyDigestMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDigestEmailListener extends QueuedListener
{
    public function handle(WeeklyDigestRequested $event): void
    {
        Mail::to($event->user->email)->send(new WeeklyDigestMail(
            stats: $event->stats,
            topProducts: $event->topProducts,
            atRiskCustomers: $event->atRiskCustomers,
            upcomingCount: $event->upcomingCount,
            storeName: $event->storeName,
            adminUrl: $event->adminUrl,
        ));
    }

    public function failed(WeeklyDigestRequested $event, \Throwable $exception): void
    {
        Log::warning('Weekly digest email failed', [
            'user' => $event->user->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
