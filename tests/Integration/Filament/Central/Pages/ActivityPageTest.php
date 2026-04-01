<?php

use App\Filament\Central\Pages\Activity;
use App\Models\Platform\PlatformActivity;
use Filament\Support\Icons\Heroicon;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new Activity;
});

test('get activities returns collection', function () {
    PlatformActivity::factory()->create(['event' => 'tenant_created', 'description' => 'New tenant']);
    PlatformActivity::factory()->create(['event' => 'plan_changed', 'description' => 'Plan upgraded']);

    $result = test()->page->getActivities();

    expect($result)->toHaveCount(2);
});

test('get action types returns array', function () {
    $result = Activity::getActionTypes();

    expect($result)->toBeArray()->toContain('created_tenant')->toContain('changed_plan')->toContain('impersonated')->toHaveCount(13);
});

test('get action color returns hex for known actions', function () {
    expect(Activity::getActionColor('created_tenant'))->toMatch('/^#[0-9a-f]{6}$/')->and(Activity::getActionColor('changed_plan'))->toMatch('/^#[0-9a-f]{6}$/')->and(Activity::getActionColor('impersonated'))->toMatch('/^#[0-9a-f]{6}$/')->and(Activity::getActionColor('unknown_action'))->toMatch('/^#[0-9a-f]{6}$/');
});

test('get event icon returns string', function () {
    expect(Activity::getEventIcon('tenant_created'))->toBeInstanceOf(Heroicon::class)->and(Activity::getEventIcon('unknown'))->toBeInstanceOf(Heroicon::class);
});

test('get event color returns hex', function () {
    expect(Activity::getEventColor('tenant_created'))->toMatch('/^#[0-9a-f]{6}$/')->and(Activity::getEventColor('tenant_deactivated'))->toMatch('/^#[0-9a-f]{6}$/');
});

test('get action category', function () {
    expect(Activity::getActionCategory('created_tenant'))->toBe('Tenant')->and(Activity::getActionCategory('changed_plan'))->toBe('Billing')->and(Activity::getActionCategory('sent_announcement'))->toBe('Communication')->and(Activity::getActionCategory('impersonated'))->toBe('Security')->and(Activity::getActionCategory('exported_data'))->toBe('Operations')->and(Activity::getActionCategory('unknown'))->toBe('Other');
});

test('audit trail filter properties exist', function () {
    expect(test()->page->filterAction)->toBeEmpty()->and(test()->page->filterSearch)->toBeEmpty()->and(test()->page->filterDateFrom)->toBeEmpty()->and(test()->page->filterDateTo)->toBeEmpty()->and(test()->page->page)->toBe(1)->and(test()->page->perPage)->toBe(20);
});

test('active tab defaults to platform', function () {
    expect(test()->page->activeTab)->toBe('platform');
});

test('reset filters', function () {
    test()->page->filterAction = 'created_tenant';
    test()->page->filterSearch = 'test';
    test()->page->page = 3;

    test()->page->resetFilters();

    expect(test()->page->filterAction)->toBeEmpty()->and(test()->page->filterSearch)->toBeEmpty()->and(test()->page->page)->toBe(1);
});
