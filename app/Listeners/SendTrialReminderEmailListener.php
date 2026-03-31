<?php

namespace App\Listeners;

use App\Events\TrialReminding;
use App\Mail\TrialReminderMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTrialReminderEmailListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

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
