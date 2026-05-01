<?php

use App\Filament\Pages\Settings\ManageSettings;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('manage settings page can save store name', function () {
    Livewire::test(ManageSettings::class)
        ->set('store_name', 'New Bakery Name')
        ->call('save');

    expect(settings('store_name'))->toBe('New Bakery Name');
});

test('manage settings page can save minimum order amounts', function () {
    Livewire::test(ManageSettings::class)
        ->set('minimum_pickup_order_amount', '10')
        ->set('minimum_delivery_order_amount', '25')
        ->call('save');

    expect(settings('minimum_pickup_order_amount'))->toBe('10')
        ->and(settings('minimum_delivery_order_amount'))->toBe('25');
});

test('minimum order amounts load from saved settings on mount', function () {
    settings(['minimum_pickup_order_amount' => '5', 'minimum_delivery_order_amount' => '20']);

    Livewire::test(ManageSettings::class)
        ->assertSet('minimum_pickup_order_amount', '5')
        ->assertSet('minimum_delivery_order_amount', '20');
});

test('delivery fee tiers round-trip as structured rows through save and reload', function () {
    settings(['delivery_fee_tiers' => json_encode([
        ['min_distance' => 0, 'max_distance' => 5, 'fee' => 3, 'description' => 'Local'],
        ['min_distance' => 5, 'max_distance' => 10, 'fee' => 5, 'description' => 'Extended'],
    ])]);

    Livewire::test(ManageSettings::class)
        ->assertSet('delivery_fee_tiers.0.min_distance', 0)
        ->assertSet('delivery_fee_tiers.0.fee', 3)
        ->assertSet('delivery_fee_tiers.1.description', 'Extended')
        ->set('delivery_fee_tiers', [
            ['min_distance' => 0, 'max_distance' => 8, 'fee' => 4, 'description' => 'Standard'],
        ])
        ->call('save');

    $stored = json_decode(settings('delivery_fee_tiers'), true);
    expect($stored)->toHaveCount(1)
        ->and($stored[0]['max_distance'])->toBe(8)
        ->and($stored[0]['fee'])->toBe(4);
});
