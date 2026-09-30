<?php

use App\Enums\Orders\PaymentMethod;
use App\Filament\Pages\Settings\ManageSettings;
use App\Models\Operations\WebhookDelivery;
use App\Models\Platform\Setting;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use App\Services\Settings\TenantSettingsDefaults;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('manage settings page can save store name', function () {
    livewire(ManageSettings::class)
        ->set('store_name', 'New Bakery Name')
        ->call('save');

    expect(settings('store_name'))->toBe('New Bakery Name');
});

test('manage settings page can save minimum order amounts', function () {
    livewire(ManageSettings::class)
        ->set('minimum_pickup_order_amount', '10')
        ->set('minimum_delivery_order_amount', '25')
        ->call('save');

    expect(settings('minimum_pickup_order_amount'))->toBe('10')
        ->and(settings('minimum_delivery_order_amount'))->toBe('25');
});

test('minimum order amounts load from saved settings on mount', function () {
    settings(['minimum_pickup_order_amount' => '5', 'minimum_delivery_order_amount' => '20']);

    livewire(ManageSettings::class)
        ->assertSet('minimum_pickup_order_amount', '5')
        ->assertSet('minimum_delivery_order_amount', '20');
});

test('manage settings page can reset form values to defaults', function () {
    $defaultStoreName = TenantSettingsDefaults::all()['store_name'];

    livewire(ManageSettings::class)
        ->set('store_name', 'Temporary Name')
        ->call('resetToDefaults')
        ->assertSet('store_name', $defaultStoreName);
});

test('delivery fee tiers round-trip as structured rows through save and reload', function () {
    settings(['delivery_fee_tiers' => json_encode([
        ['min_distance' => 0, 'max_distance' => 5, 'fee' => 3, 'description' => 'Local'],
        ['min_distance' => 5, 'max_distance' => 10, 'fee' => 5, 'description' => 'Extended'],
    ])]);

    livewire(ManageSettings::class)
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

    $component = livewire(ManageSettings::class)
        ->call('regenerateWebhookSecret');

    expect(strlen($component->get('webhook_secret')))->toBe(40)
        ->and($component->get('webhook_secret'))->not->toBe('old-secret-value')
        ->and(settings('webhook_secret'))->toBe($component->get('webhook_secret'));
});

test('sendTestWebhook persists current settings then dispatches a synthetic order.created', function () {
    Http::fake(['*' => Http::response('ok', 200)]);

    livewire(ManageSettings::class)
        ->set('webhook_url', 'https://8.8.8.8/test')
        ->set('webhook_secret', 'test-secret')
        ->call('sendTestWebhook');

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return $body['event'] === 'order.created' && ($body['data']['test'] ?? false) === true;
    });

    expect(WebhookDelivery::sole()->event)->toBe('order.created');
});

test('manage settings page renders the settings that used to be unreachable', function (string $label) {
    livewire(ManageSettings::class)
        ->set('payment_methods', [PaymentMethod::PayPal->value])
        ->assertSee($label);
})->with([
    'birthday coupon' => 'Send a Birthday Coupon',
    'birthday discount' => 'Birthday Discount (%)',
    'birthday coupon validity' => 'Birthday Coupon Valid For (days)',
    'repeat reminder days' => 'Repeat Reminder Interval (days)',
    'review requests' => 'Enable Review Requests',
    'review request delay' => 'Review Request Delay (hours)',
    'weekly digest' => 'Weekly Digest Email',
    'catering enabled' => 'Accept Catering Inquiries',
    'catering minimum guests' => 'Catering Minimum Guests',
    'catering lead time' => 'Catering Lead Time (days)',
    'store website' => 'Store Website',
    'store city' => 'Store City',
    'store state' => 'Store State',
    'store zip' => 'Store ZIP Code',
    'paypal invoice terms' => 'PayPal Invoice Terms',
    'default shelf life' => 'Default Shelf Life (days)',
]);

test('the new settings load the reader defaults on mount', function () {
    livewire(ManageSettings::class)
        ->assertSet('birthday_coupon_enabled', true)
        ->assertSet('birthday_discount_percentage', 15)
        ->assertSet('birthday_coupon_valid_days', 7)
        ->assertSet('repeat_reminder_days', 30)
        ->assertSet('review_requests_enabled', false)
        ->assertSet('review_request_delay_hours', 24)
        ->assertSet('weekly_digest_enabled', true)
        ->assertSet('catering_enabled', false)
        ->assertSet('catering_minimum_guests', 10)
        ->assertSet('catering_lead_time_days', 14)
        ->assertSet('paypal_invoice_terms', 'Payment due within 30 days.')
        ->assertSet('default_shelf_life_days', 3);
});

test('manage settings page round-trips the new settings through save and reload', function () {
    livewire(ManageSettings::class)
        ->set('birthday_discount_percentage', 20)
        ->set('review_requests_enabled', true)
        ->set('weekly_digest_enabled', false)
        ->set('catering_enabled', true)
        ->set('catering_minimum_guests', 25)
        ->set('store_website', 'https://bakery.test')
        ->set('store_city', 'Austin')
        ->set('paypal_invoice_terms', 'Due on receipt.')
        ->set('default_shelf_life_days', 5)
        ->call('save');

    $settings = TenantSettings::resolve();

    expect($settings->engagement->birthdayDiscountPercentage)->toBe(20)
        ->and($settings->engagement->reviewRequestsEnabled)->toBeTrue()
        ->and($settings->catering->enabled)->toBeTrue()
        ->and($settings->catering->minimumGuests)->toBe('25')
        ->and($settings->store->website)->toBe('https://bakery.test')
        ->and(settings('weekly_digest_enabled'))->toBe('0')
        ->and(settings('store_city'))->toBe('Austin')
        ->and(settings('paypal_invoice_terms'))->toBe('Due on receipt.')
        ->and(settings('default_shelf_life_days'))->toBe('5');

    livewire(ManageSettings::class)
        ->assertSet('birthday_discount_percentage', 20)
        ->assertSet('review_requests_enabled', true)
        ->assertSet('weekly_digest_enabled', false)
        ->assertSet('catering_enabled', true)
        ->assertSet('catering_minimum_guests', 25)
        ->assertSet('store_website', 'https://bakery.test')
        ->assertSet('store_city', 'Austin')
        ->assertSet('paypal_invoice_terms', 'Due on receipt.')
        ->assertSet('default_shelf_life_days', 5);
});

test('every key the form sends is persisted by SaveTenantSettings', function () {
    // Regression guard for the silent-drop bug shape: ManageSettings::
    // toSettingsArray() sends N keys to SaveTenantSettings; if the action
    // forgets to write any of them, the form reports success but persists
    // nothing. Has bitten us four times already (paypal, webhook,
    // 8 email toggles, 2 gift card fields). This test catches the next one.

    $page = livewire(ManageSettings::class)->instance();

    $reflection = new ReflectionMethod($page, 'toSettingsArray');
    $sentKeys = array_keys($reflection->invoke($page));

    livewire(ManageSettings::class)->call('save');

    // Check the settings table directly — settings() coalesces stored-null
    // back to the supplied default, which would mask a field saved as null.
    // We want "did a row land for this key" not "is the value non-null."
    $persistedKeys = Setting::query()->pluck('key')->all();

    expect($sentKeys)->each->toBeIn($persistedKeys);
});
