<?php

namespace App\Builders\Inventory;

use App\Models\Inventory\SeasonalItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

/** @extends Builder<SeasonalItem> */
class SeasonalItemQueryBuilder extends Builder
{
    public function current(): static
    {
        $today = Date::today();

        $this->where('available_from', '<=', $today)
            ->where('available_until', '>=', $today);

        return $this;
    }

    public function upcoming(): static
    {
        $this->where('available_from', '>', Date::today());

        return $this;
    }

    public function expired(): static
    {
        $this->where('available_until', '<', Date::today());

        return $this;
    }
}
