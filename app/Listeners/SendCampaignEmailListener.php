<?php

namespace App\Listeners;

use App\Events\CampaignEmailQueued;
use App\Mail\CustomerBlastMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCampaignEmailListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public function handle(CampaignEmailQueued $event): void
    {
        Mail::to($event->email)->send(
            new CustomerBlastMail($event->subject, $event->body),
        );
    }

    public function failed(CampaignEmailQueued $event, \Throwable $exception): void
    {
        Log::warning('Campaign email failed', [
            'email' => $event->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
