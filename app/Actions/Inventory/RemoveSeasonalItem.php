<?php

namespace App\Actions\Inventory;

use App\Models\Inventory\SeasonalItem;

class RemoveSeasonalItem
{
    public function __invoke(int $seasonalItemId): void
    {
        SeasonalItem::query()->findOrFail($seasonalItemId)->delete();
    }
}
