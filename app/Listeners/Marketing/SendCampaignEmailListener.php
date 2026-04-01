<?php

namespace App\Listeners\Marketing;

use App\Events\Marketing\CampaignEmailQueued;
use App\Listeners\QueuedListener;
use App\Mail\Marketing\CustomerBlastMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCampaignEmailListener extends QueuedListener
{
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
