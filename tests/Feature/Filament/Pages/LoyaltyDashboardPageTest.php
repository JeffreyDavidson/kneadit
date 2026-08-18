<?php

use App\Filament\Pages\Engagement\LoyaltyDashboard;
use App\Models\Staff\User;
use App\Services\Settings\SettingsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('loyalty dashboard page renders for manager', function () {
    Livewire::test(LoyaltyDashboard::class)->assertOk();
});

test('toggleLoyalty flips the setting', function () {
    $manager = resolve(SettingsManager::class);
    $manager->set('loyalty_enabled', '0');

    Livewire::test(LoyaltyDashboard::class)
        ->call('toggleLoyalty')
        ->assertSet('loyaltyEnabled', true);

    expect($manager->get('loyalty_enabled'))->toBe('1');
});
