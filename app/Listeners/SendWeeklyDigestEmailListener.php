<?php

namespace App\Listeners;

use App\Events\WeeklyDigestRequested;
use App\Mail\WeeklyDigestMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDigestEmailListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

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
