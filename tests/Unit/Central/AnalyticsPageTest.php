<?php

namespace Tests\Unit\Central;

use App\Filament\Central\Pages\Analytics;
use App\Models\Tenant;
use Tests\CentralTestCase;

class AnalyticsPageTest extends CentralTestCase
{
    use CentralTenantHelper;

    private Analytics $page;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureCentralTenantsTable();
        $this->page = new Analytics();
    }

    public function test_get_signups_by_month_returns_12_months(): void
    {
        $result = $this->page->getSignupsByMonth();

        $this->assertCount(12, $result);
        $this->assertArrayHasKey('label', $result[0]);
        $this->assertArrayHasKey('count', $result[0]);
    }

    public function test_get_plan_distribution(): void
    {
        $this->createTenant(['id' => 'b1', 'name' => 'B1', 'email' => 'b1@test.com', 'plan' => 'starter']);
        $this->createTenant(['id' => 'b2', 'name' => 'B2', 'email' => 'b2@test.com', 'plan' => 'starter']);
        $this->createTenant(['id' => 'b3', 'name' => 'B3', 'email' => 'b3@test.com', 'plan' => 'growth']);

        $result = $this->page->getPlanDistribution();

        $this->assertEquals(2, $result['starter']);
        $this->assertEquals(1, $result['growth']);
    }

    public function test_get_trial_conversion(): void
    {
        $this->createTenant(['id' => 't1', 'name' => 'T1', 'email' => 't1@test.com', 'plan' => 'starter', 'trial_ends_at' => now()->addDays(7)]);
        $this->createTenant(['id' => 't2', 'name' => 'T2', 'email' => 't2@test.com', 'plan' => 'starter', 'trial_ends_at' => now()->subDays(7)]);
        $this->createTenant(['id' => 't3', 'name' => 'T3', 'email' => 't3@test.com', 'plan' => 'growth']);

        $result = $this->page->getTrialConversion();

        $this->assertArrayHasKey('on_trial', $result);
        $this->assertArrayHasKey('expired', $result);
        $this->assertArrayHasKey('converted', $result);
        $this->assertEquals(1, $result['on_trial']);
        $this->assertEquals(1, $result['expired']);
        $this->assertEquals(1, $result['converted']);
    }

    public function test_get_total_signups(): void
    {
        $this->createTenant(['id' => 's1', 'name' => 'S1', 'email' => 's1@test.com', 'plan' => 'starter']);
        $this->createTenant(['id' => 's2', 'name' => 'S2', 'email' => 's2@test.com', 'plan' => 'growth']);

        $this->assertEquals(2, $this->page->getTotalSignups());
    }

    public function test_get_this_month_signups(): void
    {
        $this->createTenant(['id' => 'm1', 'name' => 'M1', 'email' => 'm1@test.com', 'plan' => 'starter']);
        $this->createTenant(['id' => 'm2', 'name' => 'M2', 'email' => 'm2@test.com', 'plan' => 'starter']);

        $this->assertEquals(2, $this->page->getThisMonthSignups());
    }

    public function test_get_avg_days_on_trial(): void
    {
        $this->createTenant(['id' => 'a1', 'name' => 'A1', 'email' => 'a1@test.com', 'plan' => 'starter', 'trial_ends_at' => now()->addDays(14)]);
        $this->createTenant(['id' => 'a2', 'name' => 'A2', 'email' => 'a2@test.com', 'plan' => 'starter', 'trial_ends_at' => now()->addDays(14)]);

        $result = $this->page->getAvgDaysOnTrial();

        $this->assertIsFloat($result);
        $this->assertEquals(14.0, $result);
    }

    public function test_get_avg_days_on_trial_returns_zero_when_no_trials(): void
    {
        $this->assertEquals(0, $this->page->getAvgDaysOnTrial());
    }

    public function test_get_most_popular_plan(): void
    {
        $this->createTenant(['id' => 'p1', 'name' => 'P1', 'email' => 'p1@test.com', 'plan' => 'starter']);
        $this->createTenant(['id' => 'p2', 'name' => 'P2', 'email' => 'p2@test.com', 'plan' => 'starter']);
        $this->createTenant(['id' => 'p3', 'name' => 'P3', 'email' => 'p3@test.com', 'plan' => 'growth']);

        $this->assertEquals('starter', $this->page->getMostPopularPlan());
    }

    public function test_get_most_popular_plan_returns_na_when_no_tenants(): void
    {
        $this->assertEquals('N/A', $this->page->getMostPopularPlan());
    }
}
