<?php

use App\Filament\Pages\Platform\ActivityLogPage;
use App\Models\Operations\ActivityLog;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new ActivityLogPage;
});

test('filter action defaults to null', function () {
    expect(test()->page->filterAction)->toBeNull();
});

test('filter model type defaults to null', function () {
    expect(test()->page->filterModelType)->toBeNull();
});

test('filter user defaults to null', function () {
    expect(test()->page->filterUser)->toBeNull();
});

test('page defaults to 1', function () {
    expect(test()->page->page)->toBe(1);
});

test('per page defaults to 25', function () {
    expect(test()->page->perPage)->toBe(25);
});

test('expanded id defaults to null', function () {
    expect(test()->page->expandedId)->toBeNull();
});

test('get activities property returns paginator', function () {
    ActivityLog::factory()->count(3)->create();

    $result = test()->page->getActivitiesProperty();

    expect($result)->toBeInstanceOf(Illuminate\Contracts\Pagination\LengthAwarePaginator::class)
        ->and($result->total())->toBe(3);
});

test('get activities property filters by action', function () {
    ActivityLog::factory()->create(['action' => 'created']);
    ActivityLog::factory()->create(['action' => 'updated']);
    ActivityLog::factory()->create(['action' => 'deleted']);

    test()->page->filterAction = 'created';
    $result = test()->page->getActivitiesProperty();

    expect($result->total())->toBe(1);
});

test('get activities property filters by model type', function () {
    ActivityLog::factory()->create(['model_type' => \App\Models\Orders\Order::class]);
    ActivityLog::factory()->create(['model_type' => \App\Models\Inventory\Product::class]);

    test()->page->filterModelType = \App\Models\Orders\Order::class;
    $result = test()->page->getActivitiesProperty();

    expect($result->total())->toBe(1);
});

test('get activities property filters by user name', function () {
    ActivityLog::factory()->create(['user_name' => 'John Doe']);
    ActivityLog::factory()->create(['user_name' => 'Jane Smith']);

    test()->page->filterUser = 'John';
    $result = test()->page->getActivitiesProperty();

    expect($result->total())->toBe(1);
});

test('get activities property filters by date range', function () {
    ActivityLog::factory()->create(['created_at' => now()->subDays(10)]);
    ActivityLog::factory()->create(['created_at' => now()->subDays(5)]);
    ActivityLog::factory()->create(['created_at' => now()]);

    test()->page->filterDateFrom = now()->subDays(6)->format('Y-m-d');
    $result = test()->page->getActivitiesProperty();

    expect($result->total())->toBe(2);
});

test('get action types property returns array', function () {
    ActivityLog::factory()->create(['action' => 'created']);
    ActivityLog::factory()->create(['action' => 'updated']);

    $types = test()->page->getActionTypesProperty();

    expect($types)->toBeArray()->toContain('created')->toContain('updated');
});

test('get model types property returns array of arrays', function () {
    ActivityLog::factory()->create(['model_type' => \App\Models\Orders\Order::class]);

    $types = test()->page->getModelTypesProperty();

    expect($types)->toBeArray()
        ->and($types[0])->toHaveKeys(['value', 'label']);
});

test('toggle expanded sets id', function () {
    test()->page->toggleExpanded(5);
    expect(test()->page->expandedId)->toBe(5);
});

test('toggle expanded unsets when same id', function () {
    test()->page->expandedId = 5;
    test()->page->toggleExpanded(5);
    expect(test()->page->expandedId)->toBeNull();
});

test('reset filters clears all filters', function () {
    test()->page->filterAction = 'created';
    test()->page->filterModelType = 'Order';
    test()->page->filterUser = 'John';
    test()->page->filterDateFrom = '2026-01-01';
    test()->page->filterDateTo = '2026-12-31';
    test()->page->page = 3;

    test()->page->resetFilters();

    expect(test()->page->filterAction)->toBeNull()
        ->and(test()->page->filterModelType)->toBeNull()
        ->and(test()->page->filterUser)->toBeNull()
        ->and(test()->page->filterDateFrom)->toBeNull()
        ->and(test()->page->filterDateTo)->toBeNull()
        ->and(test()->page->page)->toBe(1);
});

test('previous page decrements but not below 1', function () {
    test()->page->page = 3;
    test()->page->previousPage();
    expect(test()->page->page)->toBe(2);

    test()->page->page = 1;
    test()->page->previousPage();
    expect(test()->page->page)->toBe(1);
});

test('next page increments', function () {
    test()->page->page = 1;
    test()->page->nextPage();
    expect(test()->page->page)->toBe(2);
});

test('updated filter action resets page', function () {
    test()->page->page = 5;
    test()->page->updatedFilterAction();
    expect(test()->page->page)->toBe(1);
});

test('updated filter model type resets page', function () {
    test()->page->page = 5;
    test()->page->updatedFilterModelType();
    expect(test()->page->page)->toBe(1);
});

test('updated filter user resets page', function () {
    test()->page->page = 5;
    test()->page->updatedFilterUser();
    expect(test()->page->page)->toBe(1);
});

test('updated filter date from resets page', function () {
    test()->page->page = 5;
    test()->page->updatedFilterDateFrom();
    expect(test()->page->page)->toBe(1);
});

test('updated filter date to resets page', function () {
    test()->page->page = 5;
    test()->page->updatedFilterDateTo();
    expect(test()->page->page)->toBe(1);
});
