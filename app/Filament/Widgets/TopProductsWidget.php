<?php

namespace App\Filament\Widgets;

use App\Enums\Filament\WidgetSize;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Queries\Financial\ProductSalesQuery;
use App\ValueObjects\DateRange;
use Filament\Widgets\Widget;

class TopProductsWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    #[\Override]
    protected static ?int $sort = 7;

    #[\Override]
    protected string $view = 'filament.widgets.top-products';

    /** @return array<int, array<string, mixed>> */
    public function getProducts(): array
    {
        $limit = $this->productLimit();

        return $this->cached("main_{$limit}", [900, 1800], function () use ($limit): array {
            $products = ProductSalesQuery::topByRevenue(DateRange::thisMonth(), $limit)->all();
            $maxRevenueCents = max(array_map(
                fn (array $product): int => $product['revenue']->cents(),
                $products,
            ) ?: [1]);

            return collect($products)->map(fn (array $p): array => [
                'name' => $p['name'],
                'units_sold' => $p['units_sold'],
                'revenue' => $p['revenue']->dollars(),
                'percentage' => (int) round(($p['revenue']->cents() / $maxRevenueCents) * 100),
                'revenue_formatted' => '$'.number_format($p['revenue']->dollars(), 0),
            ])->all();
        });
    }

    private function productLimit(): int
    {
        return match ($this->size()) {
            WidgetSize::Small => 3,
            WidgetSize::Medium => 5,
            default => 7,
        };
    }

    protected function cachePrefix(): string
    {
        return 'top_products';
    }
}
