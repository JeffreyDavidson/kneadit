<?php

use App\Filament\Pages\Tools\InstagramCaptionGenerator;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('instagram caption generator page can render', function () {
    Livewire::test(InstagramCaptionGenerator::class)
        ->assertOk();
});
