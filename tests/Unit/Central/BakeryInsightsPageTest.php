<?php

namespace Tests\Unit\Central;

use App\Filament\Central\Pages\BakeryInsights;
use Tests\CentralTestCase;

class BakeryInsightsPageTest extends CentralTestCase
{
    use CentralTenantHelper;

    private BakeryInsights $page;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureCentralTenantsTable();
        $this->page = new BakeryInsights();
    }

    public function test_active_tab_defaults_to_health(): void
    {
        $this->assertEquals('health', $this->page->activeTab);
    }

    public function test_extended_trials_is_empty_array(): void
    {
        $this->assertIsArray($this->page->extendedTrials);
        $this->assertEmpty($this->page->extendedTrials);
    }

    public function test_sent_nudges_is_empty_array(): void
    {
        $this->assertIsArray($this->page->sentNudges);
        $this->assertEmpty($this->page->sentNudges);
    }

    public function test_get_next_plan(): void
    {
        $this->assertEquals('Growth', $this->page->getNextPlan('starter'));
        $this->assertEquals('Pro', $this->page->getNextPlan('growth'));
        $this->assertNull($this->page->getNextPlan('pro'));
    }

    public function test_plan_limits_constant_exists(): void
    {
        $this->assertArrayHasKey('starter', BakeryInsights::PLAN_LIMITS);
        $this->assertArrayHasKey('growth', BakeryInsights::PLAN_LIMITS);
        $this->assertArrayHasKey('pro', BakeryInsights::PLAN_LIMITS);
        $this->assertNull(BakeryInsights::PLAN_LIMITS['pro']['products']);
        $this->assertEquals(15, BakeryInsights::PLAN_LIMITS['starter']['products']);
    }

    public function test_get_health_summary_stats_returns_expected_keys(): void
    {
        $result = $this->page->getHealthSummaryStats();

        $this->assertArrayHasKey('average', $result);
        $this->assertArrayHasKey('healthy', $result);
        $this->assertArrayHasKey('at_risk', $result);
        $this->assertArrayHasKey('critical', $result);
        $this->assertArrayHasKey('total', $result);
    }

    public function test_get_health_summary_stats_with_no_tenants(): void
    {
        $result = $this->page->getHealthSummaryStats();

        $this->assertEquals(0, $result['average']);
        $this->assertEquals(0, $result['total']);
    }

    // Note: getHealthSummaryStats with tenants triggers tenancy()->initialize()
    // which requires actual tenant databases. Testing with tenants would require
    // full multi-tenancy setup, so we test the no-tenant path and properties only.
}
