<?php

use App\Filament\Pages\Operations\BakingSheet;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('baking sheet loads for selected date', function () {
    Livewire::test(BakingSheet::class)
        ->assertOk()
        ->set('selectedDate', now()->format('Y-m-d'))
        ->assertOk();
});
