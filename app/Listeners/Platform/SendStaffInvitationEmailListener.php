<?php

namespace App\Listeners\Platform;

use App\Events\Platform\StaffInvitationSent;
use App\Listeners\QueuedListener;
use App\Mail\Platform\StaffInvitationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendStaffInvitationEmailListener extends QueuedListener
{
    public function handle(StaffInvitationSent $event): void
    {
        Mail::to($event->invitation->email)->send(
            new StaffInvitationMail($event->invitation, $event->storeName, $event->acceptUrl),
        );
    }

    public function failed(StaffInvitationSent $event, \Throwable $exception): void
    {
        Log::warning('Staff invitation email failed', [
            'email' => $event->invitation->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
