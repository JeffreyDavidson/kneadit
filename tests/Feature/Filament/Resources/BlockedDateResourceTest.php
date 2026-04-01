<?php

use App\Filament\Resources\BlockedDates\Pages\ListBlockedDates;
use App\Models\Operations\BlockedDate;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can list blocked dates in the table', function () {
    $dates = BlockedDate::factory()->count(3)->create();

    Livewire::test(ListBlockedDates::class)
        ->assertCanSeeTableRecords($dates);
});

test('can create a blocked date via slide-over', function () {
    Livewire::test(ListBlockedDates::class)
        ->callAction(CreateAction::class, data: [
            'date' => '2026-12-25',
            'reason' => 'Holiday',
            'is_all_day' => true,
        ])
        ->assertHasNoFormErrors();

    expect(BlockedDate::query()->count())->toBe(1)
        ->and(BlockedDate::query()->first()->reason)->toBe('Holiday');
});

test('can edit a blocked date via table action', function () {
    $blockedDate = BlockedDate::factory()->create();

    Livewire::test(ListBlockedDates::class)
        ->callAction(TestAction::make('edit')->table($blockedDate), data: [
            'date' => $blockedDate->date->format('Y-m-d'),
            'reason' => 'Vacation',
            'is_all_day' => true,
        ])
        ->assertHasNoFormErrors();

    expect($blockedDate->fresh()->reason)->toBe('Vacation');
});

test('create blocked date validates date is required', function () {
    Livewire::test(ListBlockedDates::class)
        ->callAction(CreateAction::class, data: [
            'date' => null,
            'is_all_day' => true,
        ])
        ->assertHasFormErrors(['date' => 'required']);
});

test('can render blocked date table columns', function (string $column) {
    BlockedDate::factory()->create();

    Livewire::test(ListBlockedDates::class)
        ->assertCanRenderTableColumn($column);
})->with(['date', 'reason']);

test('can filter blocked dates by all day status', function () {
    $allDay = BlockedDate::factory()->allDay()->create();
    $partial = BlockedDate::factory()->partialDay()->create();

    Livewire::test(ListBlockedDates::class)
        ->filterTable('is_all_day', true)
        ->assertCanSeeTableRecords(collect([$allDay]))
        ->assertCanNotSeeTableRecords(collect([$partial]));
});

test('can sort blocked dates by date', function () {
    $early = BlockedDate::factory()->create(['date' => '2026-01-01']);
    $late = BlockedDate::factory()->create(['date' => '2026-12-31']);

    Livewire::test(ListBlockedDates::class)
        ->sortTable('date')
        ->assertCanSeeTableRecords(collect([$early, $late]), inOrder: true)
        ->sortTable('date', 'desc')
        ->assertCanSeeTableRecords(collect([$late, $early]), inOrder: true);
});
