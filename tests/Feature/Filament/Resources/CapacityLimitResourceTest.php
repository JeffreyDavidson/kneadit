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

test('can render capacity limit table columns', function (string $column) {
    CapacityLimit::factory()->create();

    Livewire::test(ListCapacityLimits::class)
        ->assertCanRenderTableColumn($column);
})->with(['max_orders']);

test('can edit a capacity limit via table action')
    ->todo();
