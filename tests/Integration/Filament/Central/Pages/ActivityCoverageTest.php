<?php

use App\Filament\Central\Pages\Activity;
use App\Models\Platform\AdminAuditLog;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new Activity;
});

test('get logs property returns paginator with no filters', function () {
    AdminAuditLog::factory()->count(3)->create();

    $result = test()->page->getLogsProperty();

    expect($result->total())->toBe(3);
});

test('get logs property filters by action', function () {
    AdminAuditLog::factory()->create(['action' => 'created_tenant']);
    AdminAuditLog::factory()->create(['action' => 'deleted_tenant']);

    test()->page->filterAction = 'created_tenant';
    $result = test()->page->getLogsProperty();

    expect($result->total())->toBe(1);
});

test('get logs property filters by search', function () {
    AdminAuditLog::factory()->create(['description' => 'Created bakery']);
    AdminAuditLog::factory()->create(['description' => 'Deleted something']);

    test()->page->filterSearch = 'bakery';
    $result = test()->page->getLogsProperty();

    expect($result->total())->toBe(1);
});

test('get logs property filters by date from', function () {
    AdminAuditLog::factory()->create(['created_at' => now()->subDays(10)]);
    AdminAuditLog::factory()->create(['created_at' => now()->subDays(1)]);

    test()->page->filterDateFrom = now()->subDays(3)->format('Y-m-d');
    $result = test()->page->getLogsProperty();

    expect($result->total())->toBe(1);
});

test('get logs property filters by date to', function () {
    AdminAuditLog::factory()->create(['created_at' => now()->subDays(10)]);
    AdminAuditLog::factory()->create(['created_at' => now()]);

    test()->page->filterDateTo = now()->subDays(5)->format('Y-m-d');
    $result = test()->page->getLogsProperty();

    expect($result->total())->toBe(1);
});

test('get today count property returns count', function () {
    AdminAuditLog::factory()->create(['created_at' => now()]);
    AdminAuditLog::factory()->create(['created_at' => now()->subDays(5)]);

    $result = test()->page->getTodayCountProperty();

    expect($result)->toBe(1);
});

test('get week count property returns count', function () {
    AdminAuditLog::factory()->create(['created_at' => now()]);
    AdminAuditLog::factory()->create(['created_at' => now()->subMonths(2)]);

    $result = test()->page->getWeekCountProperty();

    expect($result)->toBe(1);
});

test('get most common action property returns action string', function () {
    AdminAuditLog::factory()->count(3)->create(['action' => 'created_tenant', 'created_at' => now()]);
    AdminAuditLog::factory()->create(['action' => 'deleted_tenant', 'created_at' => now()]);

    $result = test()->page->getMostCommonActionProperty();

    expect($result)->toBe('created_tenant');
});

test('get most common action property returns dash when no logs', function () {
    $result = test()->page->getMostCommonActionProperty();

    expect($result)->toBe('—');
});
