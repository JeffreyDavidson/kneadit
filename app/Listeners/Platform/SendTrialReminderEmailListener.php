<?php

namespace App\Listeners\Platform;

use App\Events\Platform\TrialReminding;
use App\Listeners\QueuedListener;
use App\Mail\Platform\TrialReminderMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTrialReminderEmailListener extends QueuedListener
{
    public function handle(TrialReminding $event): void
    {
        Mail::to($event->user->email)->queue(new TrialReminderMail($event->user, $event->storeName, $event->daysLeft));
    }

    public function failed(TrialReminding $event, \Throwable $exception): void
    {
        Log::warning('Trial reminder email failed', [
            'email' => $event->user->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
