<?php

namespace App\Listeners;

use App\Events\CustomerBirthday;
use App\Mail\HappyBirthdayMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendHappyBirthdayEmailListener extends QueuedListener
{
    public function handle(CustomerBirthday $event): void
    {
        Mail::to($event->customer->email)->send(
            new HappyBirthdayMail($event->customer, $event->coupon),
        );
    }

    public function failed(CustomerBirthday $event, \Throwable $exception): void
    {
        Log::warning('Happy birthday email failed', [
            'customer' => $event->customer->name,
            'error' => $exception->getMessage(),
        ]);
    }
}
