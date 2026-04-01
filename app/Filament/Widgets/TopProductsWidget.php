<?php

namespace App\Filament\Widgets;

use App\Queries\Financial\ProductSalesQuery;
use App\ValueObjects\DateRange;
use Filament\Widgets\ChartWidget;

class TopProductsWidget extends ChartWidget
{
    protected static ?int $sort = 7;

    protected ?string $heading = 'Top Products This Month';

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '240px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $range = DateRange::thisMonth();

        $products = ProductSalesQuery::topByRevenue($range, 5);

        return [
            'datasets' => [
                [
                    'label' => 'Revenue ($)',
                    'data' => collect($products)->pluck('revenue')->all(),
                    'backgroundColor' => ['#8B5E3C', '#D4A574', '#F5E6D3', '#A0522D', '#DEB887'],
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $products->pluck('name')->toArray(),
        ];
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
}
