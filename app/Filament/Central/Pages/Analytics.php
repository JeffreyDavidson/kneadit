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
    private ?TenantSignupAnalyticsQuery $signupAnalyticsQuery = null;

    private ?TenantSubscriptionAnalyticsQuery $subscriptionAnalyticsQuery = null;

    private ?TenantAnalyticsSummaryQuery $analyticsSummaryQuery = null;

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    #[\Override]
    protected static ?int $navigationSort = 5;

    #[\Override]
    protected static ?string $title = 'Analytics';

    #[\Override]
    protected string $view = 'filament.central.pages.analytics';

    /** @return array<int, array<string, mixed>> */
    public function getSignupsByMonth(): array
    {
        return $this->signupAnalytics()->byMonth();
    }

    /** @return array<string, mixed> */
    public function getPlanDistribution(): array
    {
        return $this->subscriptionAnalytics()->planDistribution();
    }

    /** @return array<string, int> */
    public function getTrialConversion(): array
    {
        return $this->subscriptionAnalytics()->trialConversion();
    }

    /** @return array<int, array<string, mixed>> */
    public function getMonthlyGrowth(): array
    {
        return $this->signupAnalytics()->monthlyGrowth();
    }

    public function getTotalSignups(): int
    {
        return $this->signupAnalytics()->total();
    }

    public function getThisMonthSignups(): int
    {
        return $this->signupAnalytics()->thisMonth();
    }

    public function getAvgDaysOnTrial(): float
    {
        return $this->subscriptionAnalytics()->averageTrialDays();
    }

    public function getMostPopularPlan(): string
    {
        return $this->subscriptionAnalytics()->mostPopularPlan();
    }

    /** @return array<int, array<string, mixed>> */
    public function getKpis(): array
    {
        return $this->analyticsSummary()->kpis();
    }

    /** @return array<string, int> */
    public function getTenantStatus(): array
    {
        return $this->analyticsSummary()->tenantStatus();
    }

    private function signupAnalytics(): TenantSignupAnalyticsQuery
    {
        return $this->signupAnalyticsQuery ??= resolve(TenantSignupAnalyticsQuery::class);
    }

    private function subscriptionAnalytics(): TenantSubscriptionAnalyticsQuery
    {
        return $this->subscriptionAnalyticsQuery ??= resolve(TenantSubscriptionAnalyticsQuery::class);
    }

    private function analyticsSummary(): TenantAnalyticsSummaryQuery
    {
        return $this->analyticsSummaryQuery ??= resolve(TenantAnalyticsSummaryQuery::class);
    }
}
