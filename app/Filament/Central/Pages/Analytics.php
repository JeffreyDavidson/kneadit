<?php

namespace App\Filament\Central\Pages;

use App\Queries\Platform\TenantAnalyticsQuery;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Analytics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Analytics';

    protected string $view = 'filament.central.pages.analytics';

    /** @return array<int, array<string, mixed>> */
    public function getSignupsByMonth(): array
    {
        return TenantAnalyticsQuery::signupsByMonth();
    }

    /** @return array<string, mixed> */
    public function getPlanDistribution(): array
    {
        return TenantAnalyticsQuery::planDistribution();
    }

    /** @return array<string, int> */
    public function getTrialConversion(): array
    {
        return TenantAnalyticsQuery::trialConversion();
    }

    /** @return array<int, array<string, mixed>> */
    public function getMonthlyGrowth(): array
    {
        return TenantAnalyticsQuery::monthlyGrowth();
    }

    public function getTotalSignups(): int
    {
        return TenantAnalyticsQuery::totalSignups();
    }

    public function getThisMonthSignups(): int
    {
        return TenantAnalyticsQuery::thisMonthSignups();
    }

    public function getAvgDaysOnTrial(): float
    {
        return TenantAnalyticsQuery::avgDaysOnTrial();
    }

    public function getMostPopularPlan(): string
    {
        return TenantAnalyticsQuery::mostPopularPlan();
    }

    /** @return array<int, array<string, mixed>> */
    public function getKpis(): array
    {
        return TenantAnalyticsQuery::kpis();
    }

    /** @return array<string, int> */
    public function getTenantStatus(): array
    {
        return TenantAnalyticsQuery::tenantStatus();
    }
}
