<?php

namespace App\Listeners;

use App\Events\BirthdayDiscountGenerated;
use App\Mail\BirthdayDiscountMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBirthdayDiscountEmailListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

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
