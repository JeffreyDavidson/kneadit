<?php

use App\Filament\Resources\CapacityLimits\Pages\ListCapacityLimits;
use App\Models\CapacityLimit;
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

test('can list capacity limits in the table', function () {
    $limits = CapacityLimit::factory()->count(3)->create();

    Livewire::test(ListCapacityLimits::class)
        ->assertCanSeeTableRecords($limits);
});
