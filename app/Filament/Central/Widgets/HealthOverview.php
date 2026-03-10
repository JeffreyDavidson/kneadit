<?php

namespace App\Filament\Central\Widgets;

use App\Filament\Central\Pages\BakeryInsights;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HealthOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $healthPage = new BakeryInsights();
        $stats = $healthPage->getHealthSummaryStats();

        return [
            Stat::make('Healthy', $stats['healthy'])
                ->description('Score > 70')
                ->color('success'),
            Stat::make('At Risk', $stats['at_risk'])
                ->description('Score 40–70')
                ->color('warning'),
            Stat::make('Critical', $stats['critical'])
                ->description('Score < 40')
                ->color('danger'),
        ];
    }
}
