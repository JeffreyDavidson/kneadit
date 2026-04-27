<?php

namespace App\Filament\Widgets;

use App\Enums\Filament\WidgetSize;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Queries\Financial\ProductSalesQuery;
use App\ValueObjects\DateRange;
use Filament\Widgets\ChartWidget;

class TopProductsWidget extends ChartWidget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 7;

    protected ?string $heading = 'Top Products This Month';

    protected ?string $maxHeight = '240px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $limit = $this->productLimit();

        return $this->cached("main_{$limit}", [900, 1800], function () use ($limit): array {
            $products = ProductSalesQuery::topByRevenue(DateRange::thisMonth(), $limit);
            $palette = ['#8B5E3C', '#D4A574', '#F5E6D3', '#A0522D', '#DEB887', '#7D5A4F', '#C49A6C'];

            return [
                'datasets' => [
                    [
                        'label' => 'Revenue ($)',
                        'data' => collect($products)->pluck('revenue')->all(),
                        'backgroundColor' => array_slice($palette, 0, $limit),
                        'borderRadius' => 6,
                    ],
                ],
                'labels' => $products->pluck('name')->toArray(),
            ];
        });
    }

    private function productLimit(): int
    {
        return match ($this->size()) {
            WidgetSize::Small => 3,
            WidgetSize::Medium => 5,
            WidgetSize::Large => 7,
        };
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }

    protected function cachePrefix(): string
    {
        return 'top_products';
    }
}
