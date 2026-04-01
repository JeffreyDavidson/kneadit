<?php

namespace App\Listeners\Platform;

use App\Events\Platform\TrialExpired;
use App\Listeners\QueuedListener;
use App\Mail\Platform\TrialExpiredMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTrialExpiredEmailListener extends QueuedListener
{
    public function handle(TrialExpired $event): void
    {
        Mail::to($event->user->email)->queue(new TrialExpiredMail($event->user, $event->tenantId));
    }

    public function failed(TrialExpired $event, \Throwable $exception): void
    {
        Log::warning('Trial expired email failed', [
            'email' => $event->user->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
