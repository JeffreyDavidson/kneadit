<?php

namespace App\Builders\Operations;

use App\Models\Operations\WebhookDelivery;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<WebhookDelivery> */
class WebhookDeliveryQueryBuilder extends Builder
{
    public function failed(): static
    {
        $this->where('succeeded', false);

        return $this;
    }

    public function successful(): static
    {
        $this->where('succeeded', true);

        return $this;
    }

    public function forEvent(string $event): static
    {
        $this->where('event', $event);

        return $this;
    }

    public function recent(int $days = 30): static
    {
        $this->where('dispatched_at', '>=', now()->subDays($days));

        return $this;
    }
}
