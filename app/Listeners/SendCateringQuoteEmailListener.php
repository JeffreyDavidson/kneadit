<?php

namespace App\Listeners;

use App\Events\CateringQuoteRequested;
use App\Mail\CateringQuoteMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCateringQuoteEmailListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public function handle(CateringQuoteRequested $event): void
    {
        Mail::to($event->inquiry->customer_email)->send(new CateringQuoteMail($event->inquiry));
    }

    public function failed(CateringQuoteRequested $event, \Throwable $exception): void
    {
        Log::warning('Catering quote email failed', [
            'inquiry' => $event->inquiry->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
