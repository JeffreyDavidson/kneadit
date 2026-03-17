<?php

namespace Tests\Feature\Central;

use App\Filament\Central\Pages\BakeryInsights;
use Tests\CentralTestCase;

class BakeryInsightsTest extends CentralTestCase
{
    public function test_page_class_has_required_methods(): void
    {
        $page = new BakeryInsights;

        $this->assertTrue(method_exists($page, 'getTenantHealthData'));
        $this->assertTrue(method_exists($page, 'getAlerts'));
        $this->assertTrue(method_exists($page, 'getTenantUsageData'));
        $this->assertTrue(method_exists($page, 'getHealthSummaryStats'));
    }

    public function test_health_summary_returns_expected_keys(): void
    {
        $page = new BakeryInsights;
        $stats = $page->getHealthSummaryStats();

        $this->assertArrayHasKey('average', $stats);
        $this->assertArrayHasKey('healthy', $stats);
        $this->assertArrayHasKey('at_risk', $stats);
        $this->assertArrayHasKey('critical', $stats);
        $this->assertArrayHasKey('total', $stats);
    }

    public function test_get_next_plan_returns_correct_upgrades(): void
    {
        $page = new BakeryInsights;

        $this->assertEquals('Growth', $page->getNextPlan('starter'));
        $this->assertEquals('Pro', $page->getNextPlan('growth'));
        $this->assertNull($page->getNextPlan('pro'));
    }

    public function test_plan_limits_constant_exists(): void
    {
        $this->assertArrayHasKey('starter', BakeryInsights::PLAN_LIMITS);
        $this->assertArrayHasKey('growth', BakeryInsights::PLAN_LIMITS);
        $this->assertArrayHasKey('pro', BakeryInsights::PLAN_LIMITS);
    }
}
