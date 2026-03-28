<?php

use App\Filament\Pages\BakingSheet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('baking sheet loads for selected date', function () {
    Livewire::test(BakingSheet::class)
        ->assertOk()
        ->call('loadBakingSheet')
        ->assertOk();
});
