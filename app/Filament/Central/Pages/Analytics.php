<?php

namespace App\Filament\Central\Pages;

use App\Queries\Platform\TenantAnalyticsSummaryQuery;
use App\Queries\Platform\TenantSignupAnalyticsQuery;
use App\Queries\Platform\TenantSubscriptionAnalyticsQuery;
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
        return resolve(TenantSignupAnalyticsQuery::class)->byMonth();
    }

    /** @return array<string, mixed> */
    public function getPlanDistribution(): array
    {
        return resolve(TenantSubscriptionAnalyticsQuery::class)->planDistribution();
    }

    /** @return array<string, int> */
    public function getTrialConversion(): array
    {
        return resolve(TenantSubscriptionAnalyticsQuery::class)->trialConversion();
    }

    /** @return array<int, array<string, mixed>> */
    public function getMonthlyGrowth(): array
    {
        return resolve(TenantSignupAnalyticsQuery::class)->monthlyGrowth();
    }

    public function getTotalSignups(): int
    {
        return resolve(TenantSignupAnalyticsQuery::class)->total();
    }

    public function getThisMonthSignups(): int
    {
        return resolve(TenantSignupAnalyticsQuery::class)->thisMonth();
    }

    public function getAvgDaysOnTrial(): float
    {
        return resolve(TenantSubscriptionAnalyticsQuery::class)->averageTrialDays();
    }

    public function getMostPopularPlan(): string
    {
        return resolve(TenantSubscriptionAnalyticsQuery::class)->mostPopularPlan();
    }

    /** @return array<int, array<string, mixed>> */
    public function getKpis(): array
    {
        return resolve(TenantAnalyticsSummaryQuery::class)->kpis();
    }

    /** @return array<string, int> */
    public function getTenantStatus(): array
    {
        return resolve(TenantAnalyticsSummaryQuery::class)->tenantStatus();
    }
}
