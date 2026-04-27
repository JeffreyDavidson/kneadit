<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Services\Loyalty\LoyaltyAnalytics;
use Filament\Widgets\Widget;

class LoyaltyLeadersWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 21;

    protected string $view = 'filament.widgets.loyalty-leaders-widget';

    /** @return array<int, array<string, mixed>> */
    public function getTopCustomers(): array
    {
        return $this->cached('leaders', [1800, 3600], fn (): array => $this->analytics()->leaderboard());
    }

    public function getTotalPointsOutstanding(): int
    {
        return $this->cached('outstanding', [1800, 3600], fn (): int => $this->analytics()->outstandingPoints());
    }

    /** @return array<int, array<string, mixed>> */
    public function getRecentAwards(): array
    {
        return $this->cached('awards', [900, 1800], fn (): array => $this->analytics()->recentAwards());
    }

    private function analytics(): LoyaltyAnalytics
    {
        return resolve(LoyaltyAnalytics::class);
    }

    protected function cachePrefix(): string
    {
        return 'loyalty_leaders';
    }
}
