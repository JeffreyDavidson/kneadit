<?php

use App\Filament\Central\Pages\Activity;
use App\Models\PlatformActivity;
beforeEach(function () {
    setUpCentralTest();
    test()->page = new Activity;
});

test('get activities returns collection', function () {
    PlatformActivity::create(['event' => 'tenant_created', 'description' => 'New tenant']);
    PlatformActivity::create(['event' => 'plan_changed', 'description' => 'Plan upgraded']);

    $result = test()->page->getActivities();

    expect($result)->toHaveCount(2);
});

test('get action types returns array', function () {
    $result = Activity::getActionTypes();

    expect($result)->toBeArray();
    expect($result)->toContain('created_tenant');
    expect($result)->toContain('changed_plan');
    expect($result)->toContain('impersonated');
    expect($result)->toHaveCount(13);
});

test('get action color returns hex for known actions', function () {
    expect(Activity::getActionColor('created_tenant'))->toMatch('/^#[0-9a-f]{6}$/');
    expect(Activity::getActionColor('changed_plan'))->toMatch('/^#[0-9a-f]{6}$/');
    expect(Activity::getActionColor('impersonated'))->toMatch('/^#[0-9a-f]{6}$/');
    expect(Activity::getActionColor('unknown_action'))->toMatch('/^#[0-9a-f]{6}$/');
});

test('get event icon returns string', function () {
    expect(Activity::getEventIcon('tenant_created'))->toStartWith('heroicon-');
    expect(Activity::getEventIcon('unknown'))->toStartWith('heroicon-');
});

test('get event color returns hex', function () {
    expect(Activity::getEventColor('tenant_created'))->toMatch('/^#[0-9a-f]{6}$/');
    expect(Activity::getEventColor('tenant_deactivated'))->toMatch('/^#[0-9a-f]{6}$/');
});

test('get action category', function () {
    expect(Activity::getActionCategory('created_tenant'))->toBe('Tenant');
    expect(Activity::getActionCategory('changed_plan'))->toBe('Billing');
    expect(Activity::getActionCategory('sent_announcement'))->toBe('Communication');
    expect(Activity::getActionCategory('impersonated'))->toBe('Security');
    expect(Activity::getActionCategory('exported_data'))->toBe('Operations');
    expect(Activity::getActionCategory('unknown'))->toBe('Other');
});

test('audit trail filter properties exist', function () {
    expect(test()->page->filterAction)->toBe('');
    expect(test()->page->filterSearch)->toBe('');
    expect(test()->page->filterDateFrom)->toBe('');
    expect(test()->page->filterDateTo)->toBe('');
    expect(test()->page->page)->toBe(1);
    expect(test()->page->perPage)->toBe(20);
});

test('active tab defaults to platform', function () {
    expect(test()->page->activeTab)->toBe('platform');
});

test('reset filters', function () {
    test()->page->filterAction = 'created_tenant';
    test()->page->filterSearch = 'test';
    test()->page->page = 3;

    test()->page->resetFilters();

    expect(test()->page->filterAction)->toBe('');
    expect(test()->page->filterSearch)->toBe('');
    expect(test()->page->page)->toBe(1);
});
