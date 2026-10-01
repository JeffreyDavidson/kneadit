<?php

use App\Enums\Engagement\LoyaltyPointType;
use App\Filament\Pages\Engagement\LoyaltyDashboard;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Staff\User;
use App\Services\Settings\SettingsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('loyalty dashboard page renders for manager', function () {
    livewire(LoyaltyDashboard::class)->assertOk();
});

test('toggleLoyalty flips the setting', function () {
    $manager = resolve(SettingsManager::class);
    $manager->set('loyalty_enabled', '0');

    livewire(LoyaltyDashboard::class)
        ->call('toggleLoyalty')
        ->assertSet('loyaltyEnabled', true);

    expect($manager->get('loyalty_enabled'))->toBe('1');
});

dataset('recent activity rows', [
    'earned' => [LoyaltyPointType::Earned, 250, 'text-green-600', '+250'],
    'redeemed is stored positive and shown negative' => [LoyaltyPointType::Redeemed, 100, 'text-red-600', '-100'],
    'positive adjustment' => [LoyaltyPointType::Adjusted, 50, 'text-yellow-600', '+50'],
    'negative adjustment is not double negated' => [LoyaltyPointType::Adjusted, -25, 'text-yellow-600', '-25'],
]);

test('recent activity shows the color and sign for each point type', function (LoyaltyPointType $type, int $points, string $colorClass, string $display) {
    $customer = Customer::factory()->create();
    LoyaltyPoint::factory()->for($customer)->create(['type' => $type, 'points' => $points]);

    livewire(LoyaltyDashboard::class)
        ->assertSeeHtml("font-semibold {$colorClass}")
        ->assertSeeText($display)
        ->assertDontSeeText('+-')
        ->assertDontSeeText('--');
})->with('recent activity rows');
