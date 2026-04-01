<?php

namespace App\Listeners\Customers;

use App\Events\Customers\RepeatOrderReminderDue;
use App\Listeners\QueuedListener;
use App\Mail\Customers\RepeatOrderReminderMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRepeatOrderReminderEmailListener extends QueuedListener
{
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
