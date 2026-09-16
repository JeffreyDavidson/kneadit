<?php

namespace App\Services\Stripe;

use Closure;
use Illuminate\Support\Facades\Cache;
use Throwable;

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

    /**
     * @template TResult
     *
     * @param Closure(): TResult $callback
     * @return TResult|null
     */
    public function process(?string $eventId, Closure $callback): mixed
    {
        if (! $this->claim($eventId)) {
            return null;
        }

        try {
            $result = $callback();
            $this->complete($eventId);

            return $result;
        } catch (Throwable $exception) {
            $this->release($eventId);

            throw $exception;
        }
    }

    private function key(string $eventId): string
    {
        return "stripe_event:{$eventId}";
    }
}
