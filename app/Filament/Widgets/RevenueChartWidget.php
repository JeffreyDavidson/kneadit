<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class RevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Revenue — Last 30 Days';

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 2];

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $end = Date::today();
        $start = $end->copy()->subDays(29);
        $prevEnd = $start->copy()->subDay();
        $prevStart = $prevEnd->copy()->subDays(29);

        $current = $this->getDailyRevenue($start, $end);
        $previous = $this->getDailyRevenue($prevStart, $prevEnd);

        /** @var Collection<int, Carbon> $period */
        $period = collect(iterator_to_array(CarbonPeriod::create($start, $end)));
        $labels = $period
            ->map(fn (Carbon $d) => $d->format('M j'))
            ->all();

        $currentTotal = array_sum($current);
        $previousTotal = array_sum($previous);
        $change = $previousTotal > 0
            ? round((($currentTotal - $previousTotal) / $previousTotal) * 100, 1)
            : ($currentTotal > 0 ? 100 : 0);

        $arrow = $change >= 0 ? '↑' : '↓';
        $this->heading = "Revenue — Last 30 Days · \${$this->fmt($currentTotal)} ({$arrow} {$change}%)";

        return [
            'datasets' => [
                [
                    'label' => 'This Period',
                    'data' => $current,
                    'borderColor' => '#8B5E3C',
                    'backgroundColor' => 'rgba(139, 94, 60, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Previous Period',
                    'data' => $previous,
                    'borderColor' => '#D4A574',
                    'backgroundColor' => 'transparent',
                    'borderDash' => [5, 5],
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /** @return array<int, float> */
    private function getDailyRevenue(Carbon $start, Carbon $end): array
    {
        $raw = Order::query()->active()
            ->whereBetween('delivery_date', [$start, $end])
            ->selectRaw('DATE(delivery_date) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->pluck('revenue', 'date')
            ->toArray();

        /** @var Collection<int, Carbon> $days */
        $days = collect(iterator_to_array(CarbonPeriod::create($start, $end)));

        return $days
            ->map(fn (Carbon $d) => (float) ($raw[$d->format('Y-m-d')] ?? 0))
            ->all();
    }

    private function fmt(float $v): string
    {
        return number_format($v, 0);
    }
}
