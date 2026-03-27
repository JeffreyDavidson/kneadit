<?php

use App\Filament\Resources\BlockedDates\Pages\ListBlockedDates;
use App\Models\BlockedDate;
use App\Models\User;
use Filament\Actions\CreateAction;
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
        ->assertHasNoActionErrors();

    expect(BlockedDate::query()->count())->toBe(1)
        ->and(BlockedDate::query()->first()->reason)->toBe('Holiday');
});

test('can edit a blocked date via table action', function () {
    $blockedDate = BlockedDate::factory()->create();

    Livewire::test(ListBlockedDates::class)
        ->callTableAction('edit', $blockedDate, data: [
            'date' => $blockedDate->date->format('Y-m-d'),
            'reason' => 'Vacation',
            'is_all_day' => true,
        ])
        ->assertHasNoTableActionErrors();

    expect($blockedDate->fresh()->reason)->toBe('Vacation');
});

test('create blocked date validates date is required', function () {
    Livewire::test(ListBlockedDates::class)
        ->callAction(CreateAction::class, data: [
            'date' => null,
            'is_all_day' => true,
        ])
        ->assertHasActionErrors(['date' => 'required']);
});
