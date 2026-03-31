<?php

namespace App\Listeners;

use App\Events\HealthCheckFailed;
use App\Mail\HealthAlertMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendHealthCheckAlertListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public function handle(HealthCheckFailed $event): void
    {
        Mail::to(config('mail.platform_notify'))->send(
            new HealthAlertMail($event->message),
        );
    }

    public function failed(HealthCheckFailed $event, \Throwable $exception): void
    {
        Log::warning('Health check alert email failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
