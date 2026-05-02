<?php

use App\Filament\Pages\Settings\ManageSettings;
use App\Models\Operations\WebhookDelivery;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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

test('regenerateWebhookSecret writes a fresh 40-char secret and updates the page property', function () {
    settings(['webhook_secret' => 'old-secret-value']);

    $component = Livewire::test(ManageSettings::class)
        ->call('regenerateWebhookSecret');

    expect(strlen($component->get('webhook_secret')))->toBe(40)
        ->and($component->get('webhook_secret'))->not->toBe('old-secret-value')
        ->and(settings('webhook_secret'))->toBe($component->get('webhook_secret'));
});

test('sendTestWebhook persists current settings then dispatches a synthetic order.created', function () {
    Http::fake(['*' => Http::response('ok', 200)]);

    Livewire::test(ManageSettings::class)
        ->set('webhook_url', 'https://hooks.example.com/test')
        ->set('webhook_secret', 'test-secret')
        ->call('sendTestWebhook');

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return $body['event'] === 'order.created' && ($body['data']['test'] ?? false) === true;
    });

    expect(WebhookDelivery::sole()->event)->toBe('order.created');
});
