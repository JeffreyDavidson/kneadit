<?php

namespace App\Services\Stripe;

use Illuminate\Support\Facades\Cache;

final class StripeWebhookIdempotency
{
    public function claim(?string $eventId): bool
    {
        if (! $eventId) {
            return true;
        }

        return Cache::add($this->key($eventId), 'processing', now()->addHours(24));
    }

    public function complete(?string $eventId): void
    {
        if ($eventId) {
            Cache::put($this->key($eventId), 'processed', now()->addHours(24));
        }
    }

    public function release(?string $eventId): void
    {
        if ($eventId) {
            Cache::forget($this->key($eventId));
        }
    }

    public function alreadyProcessed(?string $eventId): bool
    {
        if (! $eventId) {
            return false;
        }

        return ! Cache::add($this->key($eventId), true, now()->addHours(24));
    }

    private function key(string $eventId): string
    {
        return "stripe_event:{$eventId}";
    }
}
