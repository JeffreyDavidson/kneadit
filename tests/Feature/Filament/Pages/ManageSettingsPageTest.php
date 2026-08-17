<?php

use App\Filament\Pages\Settings\ManageSettings;
use App\Models\Operations\WebhookDelivery;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

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

    $storedJson = settings('delivery_fee_tiers');

    if (! is_string($storedJson)) {
        throw new RuntimeException('Expected delivery fee tiers to be stored as JSON.');
    }

    $stored = json_decode($storedJson, true, flags: JSON_THROW_ON_ERROR);

    if (! is_array($stored) || ! is_array($stored[0] ?? null)) {
        throw new RuntimeException('Expected one structured delivery fee tier.');
    }

    $storedTier = $stored[0];

    expect($stored)->toHaveCount(1)
        ->and($storedTier['max_distance'] ?? null)->toBe(8)
        ->and($storedTier['fee'] ?? null)->toBe(4);
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
        ->set('webhook_url', 'https://8.8.8.8/test')
        ->set('webhook_secret', 'test-secret')
        ->call('sendTestWebhook');

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $body['event'] === 'order.created' && ($body['data']['test'] ?? false) === true;
    });

    expect(WebhookDelivery::sole()->event)->toBe('order.created');
});

test('every key the form sends is persisted by SaveTenantSettings', function () {
    // Regression guard for the silent-drop bug shape: ManageSettings::
    // toSettingsArray() sends N keys to SaveTenantSettings; if the action
    // forgets to write any of them, the form reports success but persists
    // nothing. Has bitten us four times already (paypal, webhook,
    // 8 email toggles, 2 gift card fields). This test catches the next one.

    $page = Livewire::test(ManageSettings::class)->instance();

    $reflection = new ReflectionMethod($page, 'toSettingsArray');
    $reflection->setAccessible(true);
    $sentSettings = $reflection->invoke($page);

    if (! is_array($sentSettings)) {
        throw new RuntimeException('Expected ManageSettings::toSettingsArray() to return an array.');
    }

    $sentKeys = array_keys($sentSettings);

    Livewire::test(ManageSettings::class)->call('save');

    // Check the settings table directly — settings() coalesces stored-null
    // back to the supplied default, which would mask a field saved as null.
    // We want "did a row land for this key" not "is the value non-null."
    $persistedKeys = App\Models\Platform\Setting::query()->pluck('key')->all();

    foreach ($sentKeys as $key) {
        expect(in_array($key, $persistedKeys, true))
            ->toBeTrue("settings('{$key}') was sent by ManageSettings::toSettingsArray() but never persisted by SaveTenantSettings — likely a silent-drop bug.");
    }
});
