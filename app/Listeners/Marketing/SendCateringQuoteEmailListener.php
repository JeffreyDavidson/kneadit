<?php

namespace App\Listeners\Marketing;

use App\Events\Marketing\CateringQuoteRequested;
use App\Listeners\QueuedListener;
use App\Mail\Marketing\CateringQuoteMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCateringQuoteEmailListener extends QueuedListener
{
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
