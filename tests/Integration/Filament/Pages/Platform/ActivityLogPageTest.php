<?php

use App\Enums\Operations\ActivityAction;
use App\Filament\Pages\Platform\ActivityLogPage;
use App\Models\Operations\ActivityLog;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new ActivityLogPage;
});

test('filter action defaults to null', function () {
    expect(testFixture('page', ActivityLogPage::class)->filterAction)->toBeNull();
});

test('filter model type defaults to null', function () {
    expect(testFixture('page', ActivityLogPage::class)->filterModelType)->toBeNull();
});

test('filter user defaults to null', function () {
    expect(testFixture('page', ActivityLogPage::class)->filterUser)->toBeNull();
});

test('page defaults to 1', function () {
    expect(testFixture('page', ActivityLogPage::class)->page)->toBe(1);
});

test('per page defaults to 25', function () {
    expect(testFixture('page', ActivityLogPage::class)->perPage)->toBe(25);
});

test('expanded id defaults to null', function () {
    expect(testFixture('page', ActivityLogPage::class)->expandedId)->toBeNull();
});

test('get activities property returns paginator', function () {
    ActivityLog::factory()->count(3)->create();

    $result = testFixture('page', ActivityLogPage::class)->getActivitiesProperty();

    expect($result)->toBeInstanceOf(Illuminate\Contracts\Pagination\LengthAwarePaginator::class)
        ->and($result->total())->toBe(3);
});

test('get activities property filters by action', function () {
    ActivityLog::factory()->create(['action' => 'created']);
    ActivityLog::factory()->create(['action' => 'updated']);
    ActivityLog::factory()->create(['action' => 'deleted']);

    testFixture('page', ActivityLogPage::class)->filterAction = 'created';
    $result = testFixture('page', ActivityLogPage::class)->getActivitiesProperty();

    expect($result->total())->toBe(1);
});

test('get activities property filters by model type', function () {
    ActivityLog::factory()->create(['model_type' => App\Models\Orders\Order::class]);
    ActivityLog::factory()->create(['model_type' => App\Models\Inventory\Product::class]);

    testFixture('page', ActivityLogPage::class)->filterModelType = App\Models\Orders\Order::class;
    $result = testFixture('page', ActivityLogPage::class)->getActivitiesProperty();

    expect($result->total())->toBe(1);
});

test('get activities property filters by user name', function () {
    ActivityLog::factory()->create(['user_name' => 'John Doe']);
    ActivityLog::factory()->create(['user_name' => 'Jane Smith']);

    testFixture('page', ActivityLogPage::class)->filterUser = 'John';
    $result = testFixture('page', ActivityLogPage::class)->getActivitiesProperty();

    expect($result->total())->toBe(1);
});

test('get activities property filters by date range', function () {
    ActivityLog::factory()->create(['created_at' => now()->subDays(10)]);
    ActivityLog::factory()->create(['created_at' => now()->subDays(5)]);
    ActivityLog::factory()->create(['created_at' => now()]);

    testFixture('page', ActivityLogPage::class)->filterDateFrom = now()->subDays(6)->format('Y-m-d');
    $result = testFixture('page', ActivityLogPage::class)->getActivitiesProperty();

    expect($result->total())->toBe(2);
});

test('get action options returns value→label pairs for the filter Select', function () {
    ActivityLog::factory()->create(['action' => 'created']);
    ActivityLog::factory()->create(['action' => 'updated']);

    $options = testFixture('page', ActivityLogPage::class)->getActionOptions();

    expect($options)
        ->toBeArray()
        ->toHaveKey(ActivityAction::Created->value)
        ->toHaveKey(ActivityAction::Updated->value);
});

test('get model type options returns value→class-basename pairs for the filter Select', function () {
    ActivityLog::factory()->create(['model_type' => App\Models\Orders\Order::class]);

    $options = testFixture('page', ActivityLogPage::class)->getModelTypeOptions();

    expect($options)
        ->toBeArray()
        ->toHaveKey(App\Models\Orders\Order::class)
        ->and($options[App\Models\Orders\Order::class])->toBe('Order');
});

test('toggle expanded sets id', function () {
    testFixture('page', ActivityLogPage::class)->toggleExpanded(5);
    expect(testFixture('page', ActivityLogPage::class)->expandedId)->toBe(5);
});

test('toggle expanded unsets when same id', function () {
    testFixture('page', ActivityLogPage::class)->toggleExpanded(5);
    testFixture('page', ActivityLogPage::class)->toggleExpanded(5);
    expect(testFixture('page', ActivityLogPage::class)->expandedId)->toBeNull();
});

test('reset filters clears all filters', function () {
    testFixture('page', ActivityLogPage::class)->filterAction = 'created';
    testFixture('page', ActivityLogPage::class)->filterModelType = 'Order';
    testFixture('page', ActivityLogPage::class)->filterUser = 'John';
    testFixture('page', ActivityLogPage::class)->filterDateFrom = '2026-01-01';
    testFixture('page', ActivityLogPage::class)->filterDateTo = '2026-12-31';
    testFixture('page', ActivityLogPage::class)->page = 3;

    testFixture('page', ActivityLogPage::class)->resetFilters();

    $state = get_object_vars(testFixture('page', ActivityLogPage::class));

    expect($state['filterAction'])->toBeNull()
        ->and($state['filterModelType'])->toBeNull()
        ->and($state['filterUser'])->toBeNull()
        ->and($state['filterDateFrom'])->toBeNull()
        ->and($state['filterDateTo'])->toBeNull()
        ->and($state['page'])->toBe(1);
});

test('previous page decrements but not below 1', function () {
    testFixture('page', ActivityLogPage::class)->page = 3;
    testFixture('page', ActivityLogPage::class)->previousPage();
    expect(testFixture('page', ActivityLogPage::class)->page)->toBe(2);

    testFixture('page', ActivityLogPage::class)->page = 1;
    testFixture('page', ActivityLogPage::class)->previousPage();
    expect(testFixture('page', ActivityLogPage::class)->page)->toBe(1);
});

test('next page increments', function () {
    testFixture('page', ActivityLogPage::class)->page = 1;
    testFixture('page', ActivityLogPage::class)->nextPage();
    expect(testFixture('page', ActivityLogPage::class)->page)->toBe(2);
});

test('updated filter action resets page', function () {
    testFixture('page', ActivityLogPage::class)->page = 5;
    testFixture('page', ActivityLogPage::class)->updatedFilterAction();
    expect(testFixture('page', ActivityLogPage::class)->page)->toBe(1);
});

test('updated filter model type resets page', function () {
    testFixture('page', ActivityLogPage::class)->page = 5;
    testFixture('page', ActivityLogPage::class)->updatedFilterModelType();
    expect(testFixture('page', ActivityLogPage::class)->page)->toBe(1);
});

test('updated filter user resets page', function () {
    testFixture('page', ActivityLogPage::class)->page = 5;
    testFixture('page', ActivityLogPage::class)->updatedFilterUser();
    expect(testFixture('page', ActivityLogPage::class)->page)->toBe(1);
});

test('updated filter date from resets page', function () {
    testFixture('page', ActivityLogPage::class)->page = 5;
    testFixture('page', ActivityLogPage::class)->updatedFilterDateFrom();
    expect(testFixture('page', ActivityLogPage::class)->page)->toBe(1);
});

test('updated filter date to resets page', function () {
    testFixture('page', ActivityLogPage::class)->page = 5;
    testFixture('page', ActivityLogPage::class)->updatedFilterDateTo();
    expect(testFixture('page', ActivityLogPage::class)->page)->toBe(1);
});
