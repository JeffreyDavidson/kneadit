<?php

namespace App\Http\Controllers\Stripe\Concerns;

use Illuminate\Support\Facades\Cache;

trait EnsuresWebhookIdempotency
{
    protected function claimWebhookEvent(?string $eventId): bool
    {
        if (! $eventId) {
            return true;
        }

        return Cache::add("stripe_event:{$eventId}", 'processing', now()->addHours(24));
    }

    protected function completeWebhookEvent(?string $eventId): void
    {
        if ($eventId) {
            Cache::put("stripe_event:{$eventId}", 'processed', now()->addHours(24));
        }
    }

    protected function releaseWebhookEvent(?string $eventId): void
    {
        if ($eventId) {
            Cache::forget("stripe_event:{$eventId}");
        }
    }

    protected function eventAlreadyProcessed(?string $eventId): bool
    {
        if (! $eventId) {
            return false;
        }

        return ! Cache::add("stripe_event:{$eventId}", true, now()->addHours(24));
    }
}
