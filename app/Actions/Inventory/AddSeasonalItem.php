<?php

namespace App\Actions\Inventory;

use App\Models\Inventory\SeasonalItem;

class AddSeasonalItem
{
    public function __invoke(
        int $productId,
        string $availableFrom,
        string $availableUntil,
        ?string $notes,
    ): SeasonalItem {
        return SeasonalItem::query()->create([
            'product_id' => $productId,
            'available_from' => $availableFrom,
            'available_until' => $availableUntil,
            'notes' => $notes,
        ]);
    }
}
