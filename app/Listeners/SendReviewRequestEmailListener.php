<?php

namespace App\Listeners;

use App\Events\ReviewRequested;
use App\Mail\ReviewRequestMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReviewRequestEmailListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public function handle(ReviewRequested $event): void
    {
        Mail::to($event->order->customer?->email)->send(new ReviewRequestMail($event->order));
    }

    public function failed(ReviewRequested $event, \Throwable $exception): void
    {
        Log::warning('Review request email failed', [
            'order' => $event->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
