<?php

use App\Enums\Operations\ActivityAction;
use App\Filament\Pages\Platform\ActivityLogPage;
use App\Models\Operations\ActivityLog;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new ActivityLogPage;
});

test('page state has expected defaults', function () {
    expect(test()->page->filterAction)->toBeNull()
        ->and(test()->page->filterModelType)->toBeNull()
        ->and(test()->page->filterUser)->toBeNull()
        ->and(test()->page->page)->toBe(1)
        ->and(test()->page->perPage)->toBe(25)
        ->and(test()->page->expandedId)->toBeNull();
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
    ActivityLog::factory()->create(['model_type' => App\Models\Orders\Order::class]);
    ActivityLog::factory()->create(['model_type' => App\Models\Inventory\Product::class]);

    test()->page->filterModelType = App\Models\Orders\Order::class;
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

test('get action options returns value→label pairs for the filter Select', function () {
    ActivityLog::factory()->create(['action' => 'created']);
    ActivityLog::factory()->create(['action' => 'updated']);

    $options = test()->page->getActionOptions();

    expect($options)
        ->toBeArray()
        ->toHaveKey(ActivityAction::Created->value)
        ->toHaveKey(ActivityAction::Updated->value);
});

test('get model type options returns value→class-basename pairs for the filter Select', function () {
    ActivityLog::factory()->create(['model_type' => App\Models\Orders\Order::class]);

    $options = test()->page->getModelTypeOptions();

    expect($options)
        ->toBeArray()
        ->toHaveKey(App\Models\Orders\Order::class)
        ->and($options[App\Models\Orders\Order::class])->toBe('Order');
});

test('toggle expanded sets and clears the same id', function () {
    test()->page->toggleExpanded(5);
    expect(test()->page->expandedId)->toBe(5);

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

    $state = get_object_vars(test()->page);

    expect($state['filterAction'])->toBeNull()
        ->and($state['filterModelType'])->toBeNull()
        ->and($state['filterUser'])->toBeNull()
        ->and($state['filterDateFrom'])->toBeNull()
        ->and($state['filterDateTo'])->toBeNull()
        ->and($state['page'])->toBe(1);
});

test('pagination increments and decrements without going below 1', function () {
    test()->page->page = 3;
    test()->page->previousPage();
    expect(test()->page->page)->toBe(2);

    test()->page->page = 1;
    test()->page->previousPage();
    expect(test()->page->page)->toBe(1);

    test()->page->nextPage();
    expect(test()->page->page)->toBe(2);
});

test('updated filters reset pagination', function () {
    $methods = [
        'updatedFilterAction',
        'updatedFilterModelType',
        'updatedFilterUser',
        'updatedFilterDateFrom',
        'updatedFilterDateTo',
    ];

    foreach ($methods as $method) {
        test()->page->page = 5;
        test()->page->{$method}();

        expect(test()->page->page)->toBe(1);
    }
});
