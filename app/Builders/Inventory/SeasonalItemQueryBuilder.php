<?php

declare(strict_types=1);

namespace App\Builders\Inventory;

use App\Models\Inventory\SeasonalItem;
use App\Services\Scheduling\BakeryClock;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<SeasonalItem> */
class SeasonalItemQueryBuilder extends Builder
{
    public function current(): static
    {
        $today = resolve(BakeryClock::class)->today();

        $this->whereDate('available_from', '<=', $today)
            ->whereDate('available_until', '>=', $today);

        return $this;
    }

    public function upcoming(): static
    {
        $this->whereDate('available_from', '>', resolve(BakeryClock::class)->today());

        return $this;
    }

    public function expired(): static
    {
        $this->whereDate('available_until', '<', resolve(BakeryClock::class)->today());

        return $this;
    }
}
