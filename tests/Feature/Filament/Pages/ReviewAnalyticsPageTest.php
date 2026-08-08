<?php

use App\Filament\Pages\Analytics\ReviewAnalytics;
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

test('review analytics page can render', function () {
    Livewire::test(ReviewAnalytics::class)
        ->assertOk();
});
