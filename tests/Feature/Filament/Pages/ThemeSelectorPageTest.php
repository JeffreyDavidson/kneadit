<?php

use App\Filament\Pages\ThemeSelector;
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

test('theme selector renders with available themes', function () {
    Livewire::test(ThemeSelector::class)
        ->assertOk()
        ->assertSee('Theme');
});
