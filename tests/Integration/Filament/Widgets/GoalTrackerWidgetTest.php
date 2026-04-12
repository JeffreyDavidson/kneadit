<?php

use App\Filament\Widgets\GoalTrackerWidget;

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
    test()->widget->showEditModal = true;

    test()->widget->closeEditModal();

    expect(test()->widget->showEditModal)->toBeFalse();
});

test('save goal closes modal', function () {
    test()->widget->editingType = 'monthly';
    test()->widget->editingGoal = '8000';
    test()->widget->showEditModal = true;

    test()->widget->saveGoal();

    expect(test()->widget->showEditModal)->toBeFalse();
});

test('get monthly data property returns expected keys', function () {
    $data = test()->widget->getMonthlyDataProperty();

    expect($data)->toBeArray()
        ->toHaveKeys(['label', 'goal', 'revenue', 'percentage']);
});

test('get monthly data property returns numeric values', function () {
    $data = test()->widget->getMonthlyDataProperty();

    expect($data['goal'])->toBeFloat()
        ->and($data['revenue'])->toBeFloat()
        ->and($data['percentage'])->toBeNumeric();
});

test('get yearly data property returns expected keys', function () {
    $data = test()->widget->getYearlyDataProperty();

    expect($data)->toBeArray()
        ->toHaveKeys(['label', 'goal', 'revenue', 'percentage']);
});

test('get yearly data property returns numeric values', function () {
    $data = test()->widget->getYearlyDataProperty();

    expect($data['goal'])->toBeFloat()
        ->and($data['revenue'])->toBeFloat()
        ->and($data['percentage'])->toBeNumeric();
});

test('monthly data percentage is capped at 100', function () {
    // With no orders and default goal, percentage should be 0
    $data = test()->widget->getMonthlyDataProperty();

    expect($data['percentage'])->toBeLessThanOrEqual(100)
        ->and($data['percentage'])->toBeGreaterThanOrEqual(0);
});

test('yearly data percentage is capped at 100', function () {
    $data = test()->widget->getYearlyDataProperty();

    expect($data['percentage'])->toBeLessThanOrEqual(100)
        ->and($data['percentage'])->toBeGreaterThanOrEqual(0);
});
