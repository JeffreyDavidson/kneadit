<?php

use App\Filament\Pages\Operations\BakingSheet;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('baking sheet loads for selected date', function () {
    $component = livewire(BakingSheet::class);

    $component->assertOk();
    $component->set('selectedDate', now()->format('Y-m-d'));
    $component->assertOk();
});
