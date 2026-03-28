<?php

use App\Filament\Pages\ScheduleManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('schedule manager renders and saves', function () {
    Livewire::test(ScheduleManager::class)
        ->assertOk()
        ->call('save');
});
