<?php

use App\Filament\Resources\CapacityLimits\Pages\ListCapacityLimits;
use App\Models\Operations\CapacityLimit;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can list capacity limits in the table', function () {
    $limits = CapacityLimit::factory()->count(3)->create();

    Livewire::test(ListCapacityLimits::class)
        ->assertCanSeeTableRecords($limits);
});

test('can render capacity limit table columns', function (string $column) {
    CapacityLimit::factory()->create();

    Livewire::test(ListCapacityLimits::class)
        ->assertCanRenderTableColumn($column);
})->with(['max_orders']);

test('can filter capacity limits by blocked status', function () {
    $blocked = CapacityLimit::factory()->create(['is_blocked' => true]);
    $open = CapacityLimit::factory()->create(['is_blocked' => false]);

    Livewire::test(ListCapacityLimits::class)
        ->filterTable('is_blocked', true)
        ->assertCanSeeTableRecords(collect([$blocked]))
        ->assertCanNotSeeTableRecords(collect([$open]));
});

test('can search capacity limits by notes', function () {
    $target = CapacityLimit::factory()->create(['notes' => 'Holiday rush']);
    $other = CapacityLimit::factory()->create(['notes' => 'Regular day']);

    Livewire::test(ListCapacityLimits::class)
        ->searchTable('Holiday')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can create a capacity limit for a weekday', function () {
    Livewire::test(ListCapacityLimits::class)
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
