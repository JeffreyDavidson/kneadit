<?php

use App\Enums\Orders\PaymentMethod;
use App\Filament\Pages\Settings\ManageSettings;
use App\Models\Operations\WebhookDelivery;
use App\Models\Platform\Setting;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use App\Services\Settings\TenantSettingsDefaults;
use Filament\Forms\Components\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());

    // save() validates the form, which requires a store name and at least one
    // payment method; a fresh tenant stores neither.
    settings([
        'store_name' => 'Test Bakery',
        'payment_methods' => json_encode([PaymentMethod::Cash->value]),
    ]);
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

test('every field on the settings form has a matching page property and save key', function () {
    // Guard against the silent-drop bug shape: a field is added to the form
    // schema but never wired to a property or to toSettingsArray(), so the
    // baker's change is discarded on save. The field list is derived from the
    // schema itself (hidden fields included), never hardcoded.
    $page = livewire(ManageSettings::class)->instance();

    $fieldNames = collect($page->getSchema('content')->getFlatFields(withHidden: true))
        ->map(fn (Field $field): string => $field->getName())
        ->unique()
        ->values();

    $sentKeys = array_keys(new ReflectionMethod($page, 'toSettingsArray')->invoke($page));

    expect($fieldNames)->not->toBeEmpty()
        ->and($fieldNames->reject(fn (string $name): bool => property_exists($page, $name))->values()->all())->toBeEmpty()
        ->and($fieldNames->reject(fn (string $name): bool => in_array($name, $sentKeys, true))->values()->all())->toBeEmpty();
});

test('manage settings page saves fields that used to be discarded', function (string $property, mixed $value, Closure $read) {
    livewire(ManageSettings::class)
        ->set('store_name', 'Test Bakery')
        ->set($property, $value)
        ->call('save')
        ->assertHasNoErrors();

    expect($read(TenantSettings::resolve()))->toBe($value);
})->with([
    'catering deposit percent' => ['catering_deposit_percent', 40, fn (TenantSettings $s): int => $s->catering->depositPercent],
    'low stock alerts' => ['low_stock_alerts_enabled', true, fn (TenantSettings $s): bool => $s->inventory->lowStockAlertsEnabled],
    'customer referral program' => ['customer_referral_program_enabled', true, fn (TenantSettings $s): bool => $s->engagement->customerReferralProgramEnabled],
    'customer referral discount' => ['customer_referral_discount_dollars', 15, fn (TenantSettings $s): int => $s->engagement->customerReferralDiscountDollars],
    'abandoned cart recovery' => ['abandoned_cart_recovery_enabled', true, fn (TenantSettings $s): bool => $s->engagement->abandonedCartRecoveryEnabled],
    'abandoned cart recovery hours' => ['abandoned_cart_recovery_hours', 48, fn (TenantSettings $s): int => $s->engagement->abandonedCartRecoveryHours],
    'abandoned cart recovery coupon' => ['abandoned_cart_recovery_coupon_dollars', 8, fn (TenantSettings $s): int => $s->engagement->abandonedCartRecoveryCouponDollars],
    'order modification window' => ['order_modification_window_minutes', 30, fn (TenantSettings $s): int => $s->orders->modificationWindowMinutes],
    'pickup slots' => ['pickup_slots_enabled', true, fn (TenantSettings $s): bool => $s->orders->pickupSlotsEnabled],
    'pickup slot interval' => ['pickup_slot_interval_minutes', 60, fn (TenantSettings $s): int => $s->orders->pickupSlotIntervalMinutes],
    'pickup slot capacity' => ['pickup_slot_max_per_window', 5, fn (TenantSettings $s): int => $s->orders->pickupSlotMaxPerWindow],
    'sitewide sale' => ['sitewide_sale_enabled', true, fn (TenantSettings $s): bool => $s->orders->sitewideSaleEnabled],
    'sitewide sale percent' => ['sitewide_sale_percent', 10, fn (TenantSettings $s): int => $s->orders->sitewideSalePercent],
    'sitewide sale label' => ['sitewide_sale_label', 'Summer Sale', fn (TenantSettings $s): string => $s->orders->sitewideSaleLabel],
]);

test('the previously discarded fields load the reader defaults on mount', function () {
    livewire(ManageSettings::class)
        ->assertSet('catering_deposit_percent', 25)
        ->assertSet('low_stock_alerts_enabled', false)
        ->assertSet('customer_referral_program_enabled', false)
        ->assertSet('customer_referral_discount_dollars', 10)
        ->assertSet('abandoned_cart_recovery_enabled', false)
        ->assertSet('abandoned_cart_recovery_hours', 24)
        ->assertSet('abandoned_cart_recovery_coupon_dollars', 5)
        ->assertSet('order_modification_window_minutes', 0)
        ->assertSet('pickup_slots_enabled', false)
        ->assertSet('pickup_slot_interval_minutes', 30)
        ->assertSet('pickup_slot_max_per_window', 3)
        ->assertSet('sitewide_sale_enabled', false)
        ->assertSet('sitewide_sale_percent', 0)
        ->assertSet('sitewide_sale_label', 'Sale');
});

test('manage settings page rejects invalid input on the server and saves nothing', function (string $property, mixed $value, string $rule) {
    settings(['store_name' => 'Original Bakery']);

    livewire(ManageSettings::class)
        ->set('store_name', 'Changed Bakery')
        ->set($property, $value)
        ->call('save')
        ->assertHasErrors([$property => $rule]);

    expect(settings('store_name'))->toBe('Original Bakery');
})->with([
    'birthday discount above 100' => ['birthday_discount_percentage', 500, 'max'],
    'negative review delay' => ['review_request_delay_hours', -5, 'min'],
    'malformed store website' => ['store_website', 'not a url', 'url'],
    'malformed store email' => ['store_email', 'not an email', 'email'],
    'catering deposit above 100' => ['catering_deposit_percent', 150, 'max'],
    'empty store name' => ['store_name', '', 'required'],
]);

test('manage settings page renders the loyalty program section', function (string $label) {
    livewire(ManageSettings::class)
        ->assertSee($label);
})->with([
    'section heading' => 'Loyalty Program',
    'program name' => 'Program Name',
    'points per dollar' => 'Points Earned per Dollar',
    'tiers toggle' => 'Enable Loyalty Tiers',
]);

test('the loyalty tier fields are hidden until tiers are enabled', function (string $label) {
    livewire(ManageSettings::class)
        ->assertDontSee($label)
        ->set('loyalty_tiers_enabled', true)
        ->assertSee($label);
})->with([
    'silver threshold' => 'Silver Threshold (points)',
    'gold threshold' => 'Gold Threshold (points)',
    'platinum threshold' => 'Platinum Threshold (points)',
    'tier perks toggle' => 'Enable Tier Perks',
]);

test('the loyalty perk fields are hidden until tier perks are enabled', function (string $label) {
    livewire(ManageSettings::class)
        ->set('loyalty_tiers_enabled', true)
        ->assertDontSee($label)
        ->set('loyalty_tier_perks_enabled', true)
        ->assertSee($label);
})->with([
    'silver multiplier' => 'Silver Points Multiplier',
    'gold multiplier' => 'Gold Points Multiplier',
    'platinum multiplier' => 'Platinum Points Multiplier',
    'silver free delivery' => 'Silver Free Delivery',
    'gold free delivery' => 'Gold Free Delivery',
    'platinum free delivery' => 'Platinum Free Delivery',
]);

test('the loyalty settings load the reader defaults on mount', function () {
    livewire(ManageSettings::class)
        ->assertSet('loyalty_program_name', 'Rewards')
        ->assertSet('loyalty_points_per_dollar', 10)
        ->assertSet('loyalty_tiers_enabled', false)
        ->assertSet('loyalty_tier_silver_threshold', 500)
        ->assertSet('loyalty_tier_gold_threshold', 2000)
        ->assertSet('loyalty_tier_platinum_threshold', 5000)
        ->assertSet('loyalty_tier_perks_enabled', false)
        ->assertSet('loyalty_tier_silver_multiplier', 1.0)
        ->assertSet('loyalty_tier_gold_multiplier', 1.5)
        ->assertSet('loyalty_tier_platinum_multiplier', 2.0)
        ->assertSet('loyalty_tier_silver_free_delivery', false)
        ->assertSet('loyalty_tier_gold_free_delivery', true)
        ->assertSet('loyalty_tier_platinum_free_delivery', true);
});

test('manage settings page round-trips the loyalty settings through save and reload', function () {
    livewire(ManageSettings::class)
        ->set('loyalty_program_name', 'Crumb Club')
        ->set('loyalty_points_per_dollar', 4)
        ->set('loyalty_tiers_enabled', true)
        ->set('loyalty_tier_silver_threshold', 250)
        ->set('loyalty_tier_gold_threshold', 1500)
        ->set('loyalty_tier_platinum_threshold', 4000)
        ->set('loyalty_tier_perks_enabled', true)
        ->set('loyalty_tier_silver_multiplier', 1.2)
        ->set('loyalty_tier_gold_multiplier', 1.7)
        ->set('loyalty_tier_platinum_multiplier', 3.0)
        ->set('loyalty_tier_silver_free_delivery', true)
        ->set('loyalty_tier_gold_free_delivery', false)
        ->set('loyalty_tier_platinum_free_delivery', false)
        ->call('save')
        ->assertHasNoErrors();

    $loyalty = TenantSettings::resolve()->loyalty;

    expect($loyalty->programName)->toBe('Crumb Club')
        ->and($loyalty->pointsPerDollar)->toBe(4)
        ->and($loyalty->tiersEnabled)->toBeTrue()
        ->and($loyalty->tierSilverThreshold)->toBe(250)
        ->and($loyalty->tierGoldThreshold)->toBe(1500)
        ->and($loyalty->tierPlatinumThreshold)->toBe(4000)
        ->and($loyalty->tierPerksEnabled)->toBeTrue()
        ->and($loyalty->tierSilverMultiplier)->toBe(1.2)
        ->and($loyalty->tierGoldMultiplier)->toBe(1.7)
        ->and($loyalty->tierPlatinumMultiplier)->toBe(3.0)
        ->and($loyalty->tierSilverFreeDelivery)->toBeTrue()
        ->and($loyalty->tierGoldFreeDelivery)->toBeFalse()
        ->and($loyalty->tierPlatinumFreeDelivery)->toBeFalse();

    livewire(ManageSettings::class)
        ->assertSet('loyalty_program_name', 'Crumb Club')
        ->assertSet('loyalty_points_per_dollar', 4)
        ->assertSet('loyalty_tier_gold_threshold', 1500)
        ->assertSet('loyalty_tier_silver_multiplier', 1.2)
        ->assertSet('loyalty_tier_gold_free_delivery', false);
});

test('manage settings page leaves the loyalty master switch alone', function () {
    settings(['loyalty_enabled' => '0']);

    livewire(ManageSettings::class)
        ->set('loyalty_program_name', 'Crumb Club')
        ->call('save')
        ->assertHasNoErrors();

    expect(TenantSettings::resolve()->loyalty->enabled)->toBeFalse();
});

test('manage settings page rejects invalid loyalty input on the server and saves nothing', function (array $input, string $property, string $rule) {
    settings(['loyalty_program_name' => 'Original Rewards']);

    livewire(ManageSettings::class)
        ->set('loyalty_program_name', 'Changed Rewards')
        ->set('loyalty_tiers_enabled', true)
        ->set('loyalty_tier_perks_enabled', true)
        ->set($input)
        ->call('save')
        ->assertHasErrors([$property => $rule]);

    expect(settings('loyalty_program_name'))->toBe('Original Rewards');
})->with([
    'empty program name' => [['loyalty_program_name' => ''], 'loyalty_program_name', 'required'],
    'zero points per dollar' => [['loyalty_points_per_dollar' => 0], 'loyalty_points_per_dollar', 'min'],
    'gold at or below silver' => [['loyalty_tier_silver_threshold' => 2000, 'loyalty_tier_gold_threshold' => 2000], 'loyalty_tier_gold_threshold', 'gt'],
    'platinum at or below gold' => [['loyalty_tier_gold_threshold' => 6000, 'loyalty_tier_platinum_threshold' => 5000], 'loyalty_tier_platinum_threshold', 'gt'],
    'silver multiplier below one' => [['loyalty_tier_silver_multiplier' => 0.5], 'loyalty_tier_silver_multiplier', 'min'],
    'gold multiplier below one' => [['loyalty_tier_gold_multiplier' => 0.9], 'loyalty_tier_gold_multiplier', 'min'],
    'platinum multiplier below one' => [['loyalty_tier_platinum_multiplier' => 0], 'loyalty_tier_platinum_multiplier', 'min'],
]);

test('manage settings page ignores the tier threshold order while tiers are disabled', function () {
    livewire(ManageSettings::class)
        ->set('loyalty_tiers_enabled', false)
        ->set('loyalty_tier_silver_threshold', 3000)
        ->set('loyalty_tier_gold_threshold', 2000)
        ->call('save')
        ->assertHasNoErrors();
});
