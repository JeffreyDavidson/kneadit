<?php

namespace App\Filament\Widgets;

use App\Services\Loyalty\LoyaltyAnalytics;
use Filament\Widgets\Widget;

class LoyaltyLeadersWidget extends Widget
{
    protected static ?int $sort = 21;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.loyalty-leaders-widget';

    /** @return array<int, array<string, mixed>> */
    public function getTopCustomers(): array
    {
        return $this->analytics()->leaderboard();
    }

    public function getTotalPointsOutstanding(): int
    {
        return $this->analytics()->outstandingPoints();
    }

    /** @return array<int, array<string, mixed>> */
    public function getRecentAwards(): array
    {
        return $this->analytics()->recentAwards();
    }

    private function analytics(): LoyaltyAnalytics
    {
        return resolve(LoyaltyAnalytics::class);
    }
}
