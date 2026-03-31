<?php

namespace App\Listeners;

use App\Events\BirthdayDiscountGenerated;
use App\Mail\BirthdayDiscountMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBirthdayDiscountEmailListener extends QueuedListener
{
    public function handle(BirthdayDiscountGenerated $event): void
    {
        Mail::to($event->customer->email)->send(new BirthdayDiscountMail($event->customer, $event->coupon));
    }

    public function failed(BirthdayDiscountGenerated $event, \Throwable $exception): void
    {
        Log::warning('Birthday discount email failed', [
            'customer' => $event->customer->name,
            'error' => $exception->getMessage(),
        ]);
    }
}
