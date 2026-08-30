<?php

use App\Filament\Resources\CapacityLimits\Pages\ListCapacityLimits;
use App\Models\Operations\CapacityLimit;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

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
            'day_of_week' => 'monday',
            'max_orders' => 25,
            'is_blocked' => false,
        ])
        ->assertHasNoFormErrors();

    expect(CapacityLimit::query()->first())
        ->max_orders->toBe(25)
        ->is_blocked->toBeFalse();
});
