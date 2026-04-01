<?php

use App\Filament\Pages\Tools\InstagramCaptionGenerator;
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

test('instagram caption generator page can render', function () {
    Livewire::test(InstagramCaptionGenerator::class)
        ->assertOk();
});
