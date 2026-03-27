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
