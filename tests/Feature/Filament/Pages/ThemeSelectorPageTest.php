<?php

use App\Filament\Pages\Settings\ThemeSelector;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('theme selector renders with available themes', function () {
    livewire(ThemeSelector::class)
        ->assertOk()
        ->assertSee('Theme');
});
