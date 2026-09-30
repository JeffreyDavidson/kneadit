<?php

use App\Enums\Staff\DayOfWeek;
use App\Filament\Resources\CapacityLimits\Pages\ListCapacityLimits;
use App\Models\Operations\CapacityLimit;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can list capacity limits in the table', function () {
    $limits = CapacityLimit::factory()->count(3)->create();

    livewire(ListCapacityLimits::class)
        ->assertCanSeeTableRecords($limits);
});

test('can render capacity limit table columns', function () {
    CapacityLimit::factory()->create();

    livewire(ListCapacityLimits::class)
        ->assertCanRenderTableColumn('max_orders');
});

test('can filter capacity limits by blocked status', function () {
    $blocked = CapacityLimit::factory()->blocked()->create();
    $open = CapacityLimit::factory()->open()->create();

    livewire(ListCapacityLimits::class)
        ->filterTable('is_blocked', true)
        ->assertCanSeeTableRecords(collect([$blocked]))
        ->assertCanNotSeeTableRecords(collect([$open]));
});

test('can search capacity limits by notes', function () {
    $target = CapacityLimit::factory()->create(['notes' => 'Holiday rush']);
    $other = CapacityLimit::factory()->create(['notes' => 'Regular day']);

    livewire(ListCapacityLimits::class)
        ->searchTable('Holiday')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can create a capacity limit for a weekday', function () {
    livewire(ListCapacityLimits::class)
        ->callAction('create', data: [
            'day_type' => 'monday',
            'max_orders' => 25,
            'is_blocked' => false,
        ])
        ->assertHasNoFormErrors();

    expect(CapacityLimit::query()->sole())
        ->day_of_week->toBe(DayOfWeek::Monday->value)
        ->specific_date->toBeNull()
        ->max_orders->toBe(25)
        ->is_blocked->toBeFalse();
});

test('can create capacity limits for different weekdays', function () {
    livewire(ListCapacityLimits::class)
        ->callAction('create', data: ['day_type' => 'monday', 'max_orders' => 25])
        ->assertHasNoFormErrors()
        ->callAction('create', data: ['day_type' => 'tuesday', 'max_orders' => 10])
        ->assertHasNoFormErrors();

    expect(CapacityLimit::query()->pluck('day_of_week')->sort()->values()->all())
        ->toBe([DayOfWeek::Monday->value, DayOfWeek::Tuesday->value]);
});

test('rejects a second capacity limit for the same weekday', function () {
    CapacityLimit::factory()->weekday(DayOfWeek::Monday)->create();

    livewire(ListCapacityLimits::class)
        ->callAction('create', data: ['day_type' => 'monday', 'max_orders' => 10])
        ->assertHasFormErrors(['day_type' => 'unique']);

    expect(CapacityLimit::query()->count())->toBe(1);
});

test('rejects a second capacity limit for the same date', function () {
    CapacityLimit::factory()->specificDate('2026-10-07')->create();

    livewire(ListCapacityLimits::class)
        ->callAction('create', data: [
            'day_type' => 'specific',
            'specific_date' => '2026-10-07',
            'max_orders' => 10,
        ])
        ->assertHasFormErrors(['specific_date']);

    expect(CapacityLimit::query()->count())->toBe(1);
});

test('edit form loads a weekday limit with its weekday selected', function () {
    $limit = CapacityLimit::factory()->weekday(DayOfWeek::Friday)->create();

    livewire(ListCapacityLimits::class)
        ->mountAction(TestAction::make('edit')->table($limit))
        ->assertSchemaStateSet(['day_type' => DayOfWeek::Friday->value]);
});

test('can edit a weekday limit without tripping the duplicate check', function () {
    $limit = CapacityLimit::factory()->weekday(DayOfWeek::Friday)->create(['max_orders' => 10]);

    livewire(ListCapacityLimits::class)
        ->callAction(TestAction::make('edit')->table($limit), data: ['max_orders' => 30])
        ->assertHasNoFormErrors();

    expect($limit->refresh())
        ->day_of_week->toBe(DayOfWeek::Friday->value)
        ->max_orders->toBe(30);
});

test('shows the weekday name for a weekday limit', function () {
    $limit = CapacityLimit::factory()->weekday(DayOfWeek::Friday)->create();

    livewire(ListCapacityLimits::class)
        ->assertTableColumnStateSet('day_label', 'Friday', $limit);
});

test('can create a capacity limit for a specific date', function () {
    livewire(ListCapacityLimits::class)
        ->callAction('create', data: [
            'day_type' => 'specific',
            'specific_date' => '2026-10-07',
            'max_orders' => 12,
            'notes' => 'Harvest festival',
        ])
        ->assertHasNoFormErrors();

    expect(CapacityLimit::query()->sole())
        ->specific_date->toDateString()->toBe('2026-10-07')
        ->day_of_week->toBeNull()
        ->max_orders->toBe(12)
        ->notes->toBe('Harvest festival');
});

test('validates capacity limit input', function (array $data, array $errors) {
    livewire(ListCapacityLimits::class)
        ->callAction('create', data: [
            'day_type' => 'specific',
            'specific_date' => '2026-10-07',
            'max_orders' => 10,
            ...$data,
        ])
        ->assertHasFormErrors($errors);

    expect(CapacityLimit::query()->count())->toBe(0);
})->with([
    'day type is required' => [['day_type' => null], ['day_type' => 'required']],
    'specific date is required for a specific day' => [['specific_date' => null], ['specific_date' => 'required']],
    'max orders cannot be negative' => [['max_orders' => -1], ['max_orders' => 'min']],
    'notes are limited to 500 characters' => [['notes' => str_repeat('a', 501)], ['notes' => 'max']],
]);

test('edit form loads a specific-date limit as a specific day', function () {
    $limit = CapacityLimit::factory()->specificDate('2026-10-07')->create(['max_orders' => 10]);

    livewire(ListCapacityLimits::class)
        ->mountAction(TestAction::make('edit')->table($limit))
        ->assertSchemaStateSet([
            'day_type' => 'specific',
            'max_orders' => 10,
        ]);
});

test('can edit a specific-date capacity limit', function () {
    $limit = CapacityLimit::factory()->specificDate('2026-10-07')->open()->create(['max_orders' => 10]);

    livewire(ListCapacityLimits::class)
        ->callAction(TestAction::make('edit')->table($limit), data: [
            'max_orders' => 40,
            'is_blocked' => true,
        ])
        ->assertHasNoFormErrors();

    expect($limit->refresh())
        ->specific_date->toDateString()->toBe('2026-10-07')
        ->max_orders->toBe(40)
        ->is_blocked->toBeTrue();
});

test('can delete a capacity limit', function () {
    $limit = CapacityLimit::factory()->specificDate('2026-10-07')->create();

    livewire(ListCapacityLimits::class)
        ->callAction(TestAction::make('delete')->table($limit));

    expect(CapacityLimit::query()->find($limit->id))->toBeNull();
});

test('can bulk delete capacity limits', function () {
    $kept = CapacityLimit::factory()->specificDate('2026-10-05')->create();
    $doomed = collect([
        CapacityLimit::factory()->specificDate('2026-10-06')->create(),
        CapacityLimit::factory()->specificDate('2026-10-07')->create(),
    ]);

    livewire(ListCapacityLimits::class)
        ->selectTableRecords($doomed)
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(CapacityLimit::query()->count())->toBe(1)
        ->and(CapacityLimit::query()->find($kept->id))->not->toBeNull()
        ->and(CapacityLimit::query()->find($doomed->first()->id))->toBeNull();
});

test('shows the formatted date for a specific-date limit', function () {
    $limit = CapacityLimit::factory()->specificDate('2026-10-07')->create();

    livewire(ListCapacityLimits::class)
        ->assertTableColumnStateSet('day_label', 'Wed, Oct 7, 2026', $limit);
});

test('shows unlimited when max orders is zero', function () {
    $limit = CapacityLimit::factory()->specificDate('2026-10-07')->create(['max_orders' => 0]);

    livewire(ListCapacityLimits::class)
        ->assertTableColumnFormattedStateSet('max_orders', 'Unlimited', $limit);
});

test('can sort capacity limits by day', function () {
    $earlier = CapacityLimit::factory()->specificDate('2026-10-05')->create();
    $later = CapacityLimit::factory()->specificDate('2026-10-07')->create();

    livewire(ListCapacityLimits::class)
        ->sortTable('day_label')
        ->assertCanSeeTableRecords(collect([$earlier, $later]), inOrder: true)
        ->sortTable('day_label', 'desc')
        ->assertCanSeeTableRecords(collect([$later, $earlier]), inOrder: true);
});
