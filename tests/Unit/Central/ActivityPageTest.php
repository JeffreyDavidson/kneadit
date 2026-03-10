<?php

namespace Tests\Unit\Central;

use App\Filament\Central\Pages\Activity;
use App\Models\PlatformActivity;
use Tests\CentralTestCase;

class ActivityPageTest extends CentralTestCase
{
    private Activity $page;

    protected function setUp(): void
    {
        parent::setUp();
        $this->page = new Activity();
    }

    public function test_get_activities_returns_collection(): void
    {
        PlatformActivity::create(['event' => 'tenant_created', 'description' => 'New tenant']);
        PlatformActivity::create(['event' => 'plan_changed', 'description' => 'Plan upgraded']);

        $result = $this->page->getActivities();

        $this->assertCount(2, $result);
    }

    public function test_get_action_types_returns_array(): void
    {
        $result = Activity::getActionTypes();

        $this->assertIsArray($result);
        $this->assertContains('created_tenant', $result);
        $this->assertContains('changed_plan', $result);
        $this->assertContains('impersonated', $result);
        $this->assertCount(13, $result);
    }

    public function test_get_action_color_returns_hex_for_known_actions(): void
    {
        $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', Activity::getActionColor('created_tenant'));
        $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', Activity::getActionColor('changed_plan'));
        $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', Activity::getActionColor('impersonated'));
        $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', Activity::getActionColor('unknown_action'));
    }

    public function test_get_event_icon_returns_string(): void
    {
        $this->assertStringStartsWith('heroicon-', Activity::getEventIcon('tenant_created'));
        $this->assertStringStartsWith('heroicon-', Activity::getEventIcon('unknown'));
    }

    public function test_get_event_color_returns_hex(): void
    {
        $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', Activity::getEventColor('tenant_created'));
        $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', Activity::getEventColor('tenant_deactivated'));
    }

    public function test_get_action_category(): void
    {
        $this->assertEquals('Tenant', Activity::getActionCategory('created_tenant'));
        $this->assertEquals('Billing', Activity::getActionCategory('changed_plan'));
        $this->assertEquals('Communication', Activity::getActionCategory('sent_announcement'));
        $this->assertEquals('Security', Activity::getActionCategory('impersonated'));
        $this->assertEquals('Operations', Activity::getActionCategory('exported_data'));
        $this->assertEquals('Other', Activity::getActionCategory('unknown'));
    }

    public function test_audit_trail_filter_properties_exist(): void
    {
        $this->assertEquals('', $this->page->filterAction);
        $this->assertEquals('', $this->page->filterSearch);
        $this->assertEquals('', $this->page->filterDateFrom);
        $this->assertEquals('', $this->page->filterDateTo);
        $this->assertEquals(1, $this->page->page);
        $this->assertEquals(20, $this->page->perPage);
    }

    public function test_active_tab_defaults_to_platform(): void
    {
        $this->assertEquals('platform', $this->page->activeTab);
    }

    public function test_reset_filters(): void
    {
        $this->page->filterAction = 'created_tenant';
        $this->page->filterSearch = 'test';
        $this->page->page = 3;

        $this->page->resetFilters();

        $this->assertEquals('', $this->page->filterAction);
        $this->assertEquals('', $this->page->filterSearch);
        $this->assertEquals(1, $this->page->page);
    }
}
