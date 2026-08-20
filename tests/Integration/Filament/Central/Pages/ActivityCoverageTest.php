<?php

use App\Filament\Central\Pages\Activity;
use App\Models\Platform\AdminAuditLog;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new Activity;
});

test('get logs property returns paginator with no filters', function () {
    AdminAuditLog::factory()->count(3)->create();

    $result = testFixture('page', Activity::class)->getLogsProperty();

    expect($result->total())->toBe(3);
});

test('get logs property filters by action', function () {
    AdminAuditLog::factory()->create(['action' => 'created_tenant']);
    AdminAuditLog::factory()->create(['action' => 'deleted_tenant']);

    testFixture('page', Activity::class)->filterAction = 'created_tenant';
    $result = testFixture('page', Activity::class)->getLogsProperty();

    expect($result->total())->toBe(1);
});

test('get logs property filters by search', function () {
    AdminAuditLog::factory()->create(['description' => 'Created bakery']);
    AdminAuditLog::factory()->create(['description' => 'Deleted something']);

    testFixture('page', Activity::class)->filterSearch = 'bakery';
    $result = testFixture('page', Activity::class)->getLogsProperty();

    expect($result->total())->toBe(1);
});

test('get logs property filters by date from', function () {
    AdminAuditLog::factory()->create(['created_at' => now()->subDays(10)]);
    AdminAuditLog::factory()->create(['created_at' => now()->subDays(1)]);

    testFixture('page', Activity::class)->filterDateFrom = now()->subDays(3)->format('Y-m-d');
    $result = testFixture('page', Activity::class)->getLogsProperty();

    expect($result->total())->toBe(1);
});

test('get logs property filters by date to', function () {
    AdminAuditLog::factory()->create(['created_at' => now()->subDays(10)]);
    AdminAuditLog::factory()->create(['created_at' => now()]);

    testFixture('page', Activity::class)->filterDateTo = now()->subDays(5)->format('Y-m-d');
    $result = testFixture('page', Activity::class)->getLogsProperty();

    expect($result->total())->toBe(1);
});

test('get today count property returns count', function () {
    AdminAuditLog::factory()->create(['created_at' => now()]);
    AdminAuditLog::factory()->create(['created_at' => now()->subDays(5)]);

    $result = testFixture('page', Activity::class)->getTodayCountProperty();

    expect($result)->toBe(1);
});

test('get week count property returns count', function () {
    AdminAuditLog::factory()->create(['created_at' => now()]);
    AdminAuditLog::factory()->create(['created_at' => now()->subMonths(2)]);

    $result = testFixture('page', Activity::class)->getWeekCountProperty();

    expect($result)->toBe(1);
});

test('get most common action property returns action string', function () {
    AdminAuditLog::factory()->count(3)->create(['action' => 'created_tenant', 'created_at' => now()]);
    AdminAuditLog::factory()->create(['action' => 'deleted_tenant', 'created_at' => now()]);

    $result = testFixture('page', Activity::class)->getMostCommonActionProperty();

    expect($result)->toBe('created_tenant');
});

test('get most common action property returns dash when no logs', function () {
    $result = testFixture('page', Activity::class)->getMostCommonActionProperty();

    expect($result)->toBe('—');
});

test('previous page decrements but stays at 1', function () {
    testFixture('page', Activity::class)->page = 1;
    testFixture('page', Activity::class)->previousPage();

    expect(testFixture('page', Activity::class)->page)->toBe(1);
});

test('previous page decrements from higher page', function () {
    testFixture('page', Activity::class)->page = 3;
    testFixture('page', Activity::class)->previousPage();

    expect(testFixture('page', Activity::class)->page)->toBe(2);
});

test('next page increments', function () {
    testFixture('page', Activity::class)->page = 1;
    testFixture('page', Activity::class)->nextPage();

    expect(testFixture('page', Activity::class)->page)->toBe(2);
});

test('get event icon returns icon for all known events', function (string $event) {
    $result = Activity::getEventIcon($event);

    expect($result)->toBeInstanceOf(Filament\Support\Icons\Heroicon::class);
})->with([
    'tenant_created',
    'tenant_deactivated',
    'plan_changed',
    'storefront_toggled',
    'trial_expired',
]);

test('get event color returns hex for all known events', function (string $event) {
    $result = Activity::getEventColor($event);

    expect($result)->toMatch('/^#[0-9a-f]{6}$/');
})->with([
    'tenant_created',
    'tenant_deactivated',
    'plan_changed',
    'storefront_toggled',
    'trial_expired',
]);

test('get action color returns hex for all known actions', function (string $action) {
    $result = Activity::getActionColor($action);

    expect($result)->toMatch('/^#[0-9a-f]{6}$/');
})->with([
    'created_tenant',
    'updated_tenant',
    'deleted_tenant',
    'activated',
    'deactivated',
    'changed_plan',
    'extended_trial',
    'sent_announcement',
    'sent_campaign',
    'sent_message',
    'impersonated',
    'exported_data',
    'toggled_maintenance',
]);

test('get action category returns category for all known actions', function (string $action, string $expected) {
    expect(Activity::getActionCategory($action))->toBe($expected);
})->with([
    ['created_tenant', 'Tenant'],
    ['updated_tenant', 'Tenant'],
    ['deleted_tenant', 'Tenant'],
    ['activated', 'Tenant'],
    ['deactivated', 'Tenant'],
    ['changed_plan', 'Billing'],
    ['extended_trial', 'Billing'],
    ['sent_announcement', 'Communication'],
    ['sent_campaign', 'Communication'],
    ['sent_message', 'Communication'],
    ['impersonated', 'Security'],
    ['exported_data', 'Operations'],
    ['toggled_maintenance', 'Operations'],
]);
