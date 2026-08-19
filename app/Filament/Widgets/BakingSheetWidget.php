<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Queries\Orders\BakingSheetQuery;
use Filament\Widgets\Widget;

class BakingSheetWidget extends Widget
{
    use HasDashboardSize;

    protected static ?int $sort = 3;

    protected string $view = 'filament.widgets.baking-sheet';

    /**
     * Hide when there's nothing to bake — empty "Nothing to bake!"
     * tile on a busy ops dashboard is just dead space. Reappears
     * the moment any pending/confirmed/baking order item exists for
     * today (or a confirmed order item ahead of today).
     */
    public static function canView(): bool
    {
        return BakingSheetQuery::hasDashboardItems();
    }

    /** @return array<int, array{product_id: int, name: string, quantity: int}> */
    public function getRows(): array
    {
        return BakingSheetQuery::forDashboard();
    }
}
