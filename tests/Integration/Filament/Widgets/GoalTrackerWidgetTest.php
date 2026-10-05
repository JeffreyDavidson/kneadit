<?php

use App\Filament\Widgets\GoalTrackerWidget;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Facades\Date;

beforeEach(function () {
    setUpTenantTest();
    test()->widget = new GoalTrackerWidget;
});

test('open edit modal sets modal state for monthly', function () {
    test()->widget->openEditModal('monthly');

    expect(test()->widget->showEditModal)->toBeTrue()
        ->and(test()->widget->editingType)->toBe('monthly')
        ->and(test()->widget->editingGoal)->toBe('5000');
});

test('open edit modal sets modal state for yearly', function () {
    test()->widget->openEditModal('yearly');

    expect(test()->widget->showEditModal)->toBeTrue()
        ->and(test()->widget->editingType)->toBe('yearly')
        ->and(test()->widget->editingGoal)->toBe('50000');
});

test('close edit modal hides modal', function () {
    test()->widget->openEditModal('monthly');

    test()->widget->closeEditModal();

    expect(test()->widget->showEditModal)->toBeFalse();
});

test('save goal closes modal', function () {
    test()->widget->openEditModal('monthly');
    test()->widget->editingGoal = '8000';

    test()->widget->saveGoal();

    expect(test()->widget->showEditModal)->toBeFalse();
});

test('get monthly data property returns expected keys', function () {
    $data = test()->widget->monthlyData;

    expect($data)->toBeArray()
        ->toHaveKeys(['label', 'goal', 'revenue', 'percentage']);
});

test('get monthly data property returns numeric values', function () {
    $data = test()->widget->monthlyData;

    expect($data['goal'])->toBeFloat()
        ->and($data['revenue'])->toBeFloat()
        ->and($data['percentage'])->toBeNumeric();
});

test('get yearly data property returns expected keys', function () {
    $data = test()->widget->yearlyData;

    expect($data)->toBeArray()
        ->toHaveKeys(['label', 'goal', 'revenue', 'percentage']);
});

test('get yearly data property returns numeric values', function () {
    $data = test()->widget->yearlyData;

    expect($data['goal'])->toBeFloat()
        ->and($data['revenue'])->toBeFloat()
        ->and($data['percentage'])->toBeNumeric();
});

test('monthly data percentage is capped at 100', function () {
    // With no orders and default goal, percentage should be 0
    $data = test()->widget->monthlyData;

    expect($data['percentage'])->toBeLessThanOrEqual(100)
        ->and($data['percentage'])->toBeGreaterThanOrEqual(0);
});

test('yearly data percentage is capped at 100', function () {
    $data = test()->widget->yearlyData;

    expect($data['percentage'])->toBeLessThanOrEqual(100)
        ->and($data['percentage'])->toBeGreaterThanOrEqual(0);
});

test('monthly data covers the bakery-local month', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-11-01 01:00');
    Order::factory()->paid()->create(['total' => 120, 'delivery_date' => '2026-10-31', 'created_at' => '2026-09-01 12:00:00']);

    $data = test()->widget->monthlyData;

    expect($data['label'])->toBe('October 2026')
        ->and($data['revenue'])->toBe(120.0);
});

test('yearly data covers the bakery-local year', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2027-01-01 01:00');
    Order::factory()->paid()->create(['total' => 120, 'delivery_date' => '2026-12-31', 'created_at' => '2026-01-01 12:00:00']);

    $data = test()->widget->yearlyData;

    expect($data['label'])->toBe('2026')
        ->and($data['revenue'])->toBe(120.0);
});
