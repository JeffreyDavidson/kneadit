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

    protected static ?int $sort = 7;

    protected string $view = 'filament.widgets.top-products';

    /** @return array<int, array<string, mixed>> */
    public function getProducts(): array
    {
        $limit = $this->productLimit();

        return $this->cached("main_{$limit}", [900, 1800], function () use ($limit): array {
            $products = ProductSalesQuery::topByRevenue(DateRange::thisMonth(), $limit)->all();
            $maxRevenue = collect($products)->max('revenue') ?: 1;

            return collect($products)->map(fn (array $p): array => [
                'name' => $p['name'],
                'units_sold' => $p['units_sold'],
                'revenue' => $p['revenue'],
                'percentage' => (int) round(($p['revenue'] / $maxRevenue) * 100),
                'revenue_formatted' => '$' . number_format((float) $p['revenue'], 0),
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
