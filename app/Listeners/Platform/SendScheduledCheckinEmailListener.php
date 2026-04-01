<?php

namespace App\Listeners\Platform;

use App\Events\Platform\ScheduledCheckinDue;
use App\Listeners\QueuedListener;
use App\Mail\Platform\ScheduledCheckinMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendScheduledCheckinEmailListener extends QueuedListener
{
    public function handle(ScheduledCheckinDue $event): void
    {
        Mail::to($event->tenantEmail)->send(
            new ScheduledCheckinMail($event->body, $event->subject),
        );
    }

    public function failed(ScheduledCheckinDue $event, \Throwable $exception): void
    {
        Log::warning('Scheduled checkin email failed', [
            'email' => $event->tenantEmail,
            'error' => $exception->getMessage(),
        ]);
    }
}
