<?php

namespace App\Listeners;

use App\Events\CustomerBirthday;
use App\Mail\HappyBirthdayMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendHappyBirthdayEmailListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

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
