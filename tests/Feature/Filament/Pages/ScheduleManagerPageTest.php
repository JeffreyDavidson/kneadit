<?php

use App\Filament\Pages\Operations\ScheduleManager;
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

test('schedule manager renders and saves', function () {
    Livewire::test(ScheduleManager::class)
        ->assertOk()
        ->call('save');
});
