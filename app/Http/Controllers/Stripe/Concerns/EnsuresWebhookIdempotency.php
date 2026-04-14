<?php

namespace App\Http\Controllers\Stripe\Concerns;

use Illuminate\Support\Facades\Cache;

trait EnsuresWebhookIdempotency
{
    protected function eventAlreadyProcessed(?string $eventId): bool
    {
        if (! $eventId) {
            return false;
        }

        return ! Cache::add("stripe_event:{$eventId}", true, now()->addHours(24));
    }
}
