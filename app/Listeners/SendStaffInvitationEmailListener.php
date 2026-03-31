<?php

namespace App\Listeners;

use App\Events\StaffInvitationSent;
use App\Mail\StaffInvitationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendStaffInvitationEmailListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

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
