<?php

namespace App\Listeners;

use App\Events\RepeatOrderReminderDue;
use App\Mail\RepeatOrderReminderMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRepeatOrderReminderEmailListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public function handle(RepeatOrderReminderDue $event): void
    {
        Mail::to($event->customer->email)->send(
            new RepeatOrderReminderMail($event->customer, $event->daysSinceLastOrder),
        );
    }

    public function failed(RepeatOrderReminderDue $event, \Throwable $exception): void
    {
        Log::warning('Repeat order reminder email failed', [
            'customer' => $event->customer->name,
            'error' => $exception->getMessage(),
        ]);
    }
}
