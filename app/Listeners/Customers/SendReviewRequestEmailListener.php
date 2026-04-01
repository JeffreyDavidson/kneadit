<?php

namespace App\Listeners\Customers;

use App\Events\Customers\ReviewRequested;
use App\Listeners\QueuedListener;
use App\Mail\Customers\ReviewRequestMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReviewRequestEmailListener extends QueuedListener
{
    public function handle(ReviewRequested $event): void
    {
        $event->order->loadMissing('orderItems.product');

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
