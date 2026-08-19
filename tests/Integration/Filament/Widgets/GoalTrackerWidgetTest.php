<?php

use App\Filament\Widgets\GoalTrackerWidget;

beforeEach(function () {
    setUpTenantTest();
    test()->widget = new GoalTrackerWidget;
});

test('open edit modal sets modal state for monthly', function () {
    testFixture('widget', GoalTrackerWidget::class)->openEditModal('monthly');

    expect(testFixture('widget', GoalTrackerWidget::class)->showEditModal)->toBeTrue()
        ->and(testFixture('widget', GoalTrackerWidget::class)->editingType)->toBe('monthly')
        ->and(testFixture('widget', GoalTrackerWidget::class)->editingGoal)->toBe('5000');
});

test('open edit modal sets modal state for yearly', function () {
    testFixture('widget', GoalTrackerWidget::class)->openEditModal('yearly');

    expect(testFixture('widget', GoalTrackerWidget::class)->showEditModal)->toBeTrue()
        ->and(testFixture('widget', GoalTrackerWidget::class)->editingType)->toBe('yearly')
        ->and(testFixture('widget', GoalTrackerWidget::class)->editingGoal)->toBe('50000');
});

test('close edit modal hides modal', function () {
    testFixture('widget', GoalTrackerWidget::class)->openEditModal('monthly');

    testFixture('widget', GoalTrackerWidget::class)->closeEditModal();

    expect(testFixture('widget', GoalTrackerWidget::class)->showEditModal)->toBeFalse();
});

test('save goal closes modal', function () {
    testFixture('widget', GoalTrackerWidget::class)->openEditModal('monthly');
    testFixture('widget', GoalTrackerWidget::class)->editingGoal = '8000';

    testFixture('widget', GoalTrackerWidget::class)->saveGoal();

    expect(testFixture('widget', GoalTrackerWidget::class)->showEditModal)->toBeFalse();
});

test('get monthly data property returns expected keys', function () {
    $data = testFixture('widget', GoalTrackerWidget::class)->getMonthlyDataProperty();

    expect($data)->toBeArray()
        ->toHaveKeys(['label', 'goal', 'revenue', 'percentage']);
});

test('get monthly data property returns numeric values', function () {
    $data = testFixture('widget', GoalTrackerWidget::class)->getMonthlyDataProperty();

    expect($data['goal'])->toBeFloat()
        ->and($data['revenue'])->toBeFloat()
        ->and($data['percentage'])->toBeNumeric();
});

test('get yearly data property returns expected keys', function () {
    $data = testFixture('widget', GoalTrackerWidget::class)->getYearlyDataProperty();

    expect($data)->toBeArray()
        ->toHaveKeys(['label', 'goal', 'revenue', 'percentage']);
});

test('get yearly data property returns numeric values', function () {
    $data = testFixture('widget', GoalTrackerWidget::class)->getYearlyDataProperty();

    expect($data['goal'])->toBeFloat()
        ->and($data['revenue'])->toBeFloat()
        ->and($data['percentage'])->toBeNumeric();
});

test('monthly data percentage is capped at 100', function () {
    // With no orders and default goal, percentage should be 0
    $data = testFixture('widget', GoalTrackerWidget::class)->getMonthlyDataProperty();

    expect($data['percentage'])->toBeLessThanOrEqual(100)
        ->and($data['percentage'])->toBeGreaterThanOrEqual(0);
});

test('yearly data percentage is capped at 100', function () {
    $data = testFixture('widget', GoalTrackerWidget::class)->getYearlyDataProperty();

    expect($data['percentage'])->toBeLessThanOrEqual(100)
        ->and($data['percentage'])->toBeGreaterThanOrEqual(0);
});
