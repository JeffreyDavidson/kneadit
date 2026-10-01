<?php

use App\Actions\Orders\CreateOrder;
use App\DataTransferObjects\Settings\OnboardingSettings;
use App\Enums\Orders\DeliveryType;
use App\Exceptions\Orders\InsufficientStockException;
use App\Exceptions\Orders\MinimumOrderAmountNotMetException;
use App\Exceptions\Orders\NoOrderableItemsException;
use App\Exceptions\Orders\PickupSlotUnavailableException;
use App\Models\Financial\Coupon;
use App\Models\Financial\GiftCard;
use App\Models\Inventory\Product;
use App\Models\Operations\BusinessSchedule;
use App\Models\Orders\Order;
use App\Models\Platform\Setting;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use App\Services\Stripe\StripeCheckoutService;
use Closure;
use JMac\Testing\Double;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();

    // Prevent StripeCheckoutService from hitting real Stripe API
    config(['cashier.secret' => 'sk_test_fake']);

    // Bind TenantSettings with a 1-day lead time so delivery_date validation passes
    app()->instance(TenantSettings::class, makeTenantSettings(
        store: makeStoreInfo(['name' => 'Test']),
        onboarding: new OnboardingSettings(completedAt: now()->toDateTimeString()),
    ));
});

test('successful order creation redirects to confirmation', function () {
    $order = Order::factory()->create();

    $createOrder = Double::for(CreateOrder::class);
    $createOrder->expects('__invoke')->returns($order);
    app()->instance(CreateOrder::class, $createOrder);

    $stripeService = Double::for(StripeCheckoutService::class);
    $stripeService->expects('redirectToCheckout')->returns(null);
    app()->instance(StripeCheckoutService::class, $stripeService);

    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_type' => 'pickup',
            'delivery_date' => now()->addDays(2)->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

    $response->assertRedirect()
        ->assertSessionHas('success', 'Order submitted successfully!');
});

test('returns error when date is fully booked', function () {
    $createOrder = Double::for(CreateOrder::class);
    $createOrder->expects('__invoke')->returns(null);
    app()->instance(CreateOrder::class, $createOrder);

    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_type' => 'pickup',
            'delivery_date' => now()->addDays(2)->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors(['delivery_date']);
});

test('redirects to stripe checkout when payment url is returned', function () {
    $order = Order::factory()->create();

    $createOrder = Double::for(CreateOrder::class);
    $createOrder->expects('__invoke')->returns($order);
    app()->instance(CreateOrder::class, $createOrder);

    $stripeService = Double::for(StripeCheckoutService::class);
    $stripeService->expects('redirectToCheckout')
        ->returns('https://checkout.stripe.com/pay/cs_test_abc123');
    app()->instance(StripeCheckoutService::class, $stripeService);

    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_type' => 'pickup',
            'delivery_date' => now()->addDays(2)->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

    $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_abc123');
});

test('validation fails when required fields are missing', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), []);

    $response->assertSessionHasErrors([
        'customer_name',
        'customer_email',
        'delivery_type',
        'delivery_date',
        'items',
    ]);
});

test('gift card redemption requires the matching gift card code', function () {
    $product = Product::factory()->create();
    $giftCard = GiftCard::factory()->withBalance(25.00)->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_type' => 'pickup',
            'delivery_date' => now()->addDays(2)->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            'gift_card_id' => $giftCard->id,
        ]);

    $response->assertSessionHasErrors('gift_card_code');
});

test('gift card redemption rejects a mismatched gift card code', function () {
    $product = Product::factory()->create();
    $giftCard = GiftCard::factory()->withBalance(25.00)->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_type' => 'pickup',
            'delivery_date' => now()->addDays(2)->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            'gift_card_id' => $giftCard->id,
            'gift_card_code' => 'GIFT-WRONG-CODE',
        ]);

    $response->assertSessionHasErrors('gift_card_id');
});

test('returns error when order subtotal is below minimum', function () {
    $createOrder = Double::for(CreateOrder::class);
    $createOrder->expects('__invoke')
        ->throws(new MinimumOrderAmountNotMetException(
            deliveryType: 'pickup',
            subtotal: 5.00,
            minimum: 15.00,
        ));
    app()->instance(CreateOrder::class, $createOrder);

    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_type' => 'pickup',
            'delivery_date' => now()->addDays(2)->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors(['items']);

    expect(session('errors')->first('items'))->toContain('Minimum pickup order is $15.00');
});

test('success flash message can be customized via page content', function () {
    Setting::factory()->create([
        'key' => 'page_content',
        'value' => json_encode([
            'order' => ['flash_success' => 'Yay! Your order is in.'],
        ]),
    ]);
    resolve(SettingsManager::class)->flushCache();

    $order = Order::factory()->create();

    $createOrder = Double::for(CreateOrder::class);
    $createOrder->expects('__invoke')->returns($order);
    app()->instance(CreateOrder::class, $createOrder);

    $stripeService = Double::for(StripeCheckoutService::class);
    $stripeService->expects('redirectToCheckout')->returns(null);
    app()->instance(StripeCheckoutService::class, $stripeService);

    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_type' => 'pickup',
            'delivery_date' => now()->addDays(2)->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

    $response->assertRedirect()
        ->assertSessionHas('success', 'Yay! Your order is in.');
});

test('fully booked error message can be customized via page content', function () {
    Setting::factory()->create([
        'key' => 'page_content',
        'value' => json_encode([
            'order' => ['flash_full' => 'No room on that day, friend.'],
        ]),
    ]);
    resolve(SettingsManager::class)->flushCache();

    $createOrder = Double::for(CreateOrder::class);
    $createOrder->expects('__invoke')->returns(null);
    app()->instance(CreateOrder::class, $createOrder);

    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_type' => 'pickup',
            'delivery_date' => now()->addDays(2)->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

    $response->assertSessionHasErrors(['delivery_date' => 'No room on that day, friend.']);
});

test('validation fails with invalid email', function () {
    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'not-an-email',
            'delivery_type' => 'pickup',
            'delivery_date' => now()->addDays(2)->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

    $response->assertSessionHasErrors(['customer_email']);
});

test('storefront delivery orders pass validation with a bakery delivery tier', function (bool $deliveryEnabled, bool $accepted) {
    app()->instance(TenantSettings::class, makeTenantSettings(
        orders: makeOrderSettings([
            'deliveryEnabled' => $deliveryEnabled,
            'deliveryFeeTiers' => [
                ['min_distance' => 0, 'max_distance' => 5, 'fee' => 3.00, 'description' => 'Local'],
            ],
        ]),
        store: makeStoreInfo(['name' => 'Test']),
        onboarding: new OnboardingSettings(completedAt: now()->toDateTimeString()),
    ));
    $createOrder = Double::for(CreateOrder::class);
    $createOrder->allows('__invoke')->returns(Order::factory()->create());
    app()->instance(CreateOrder::class, $createOrder);
    $stripeService = Double::for(StripeCheckoutService::class);
    $stripeService->allows('redirectToCheckout')->returns(null);
    app()->instance(StripeCheckoutService::class, $stripeService);
    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_type' => 'delivery',
            'delivery_address' => '1 Main St',
            'delivery_tier' => '0',
            'delivery_date' => now()->addDays(3)->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

    $accepted
        ? $response->assertSessionHasNoErrors()
        : $response->assertSessionHasErrors(['delivery_type']);
})->with([
    'delivery on' => [true, true],
    'delivery off' => [false, false],
]);

test('storefront order only applies a coupon redeemed by its code', function (string $couponField, string $couponValue, float $expectedDiscount, int $expectedUses) {
    $stripeService = Double::for(StripeCheckoutService::class);
    $stripeService->allows('redirectToCheckout')->returns(null);
    app()->instance(StripeCheckoutService::class, $stripeService);
    $product = Product::factory()->create(['price' => 20.00]);
    $coupon = Coupon::factory()->fixed()->create([
        'code' => 'BACK-PRIVATE',
        'fixed_amount' => 5.00,
        'max_uses' => 1,
    ]);
    $payload = [
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@example.com',
        'delivery_type' => 'pickup',
        'delivery_date' => now()->addDays(2)->toDateString(),
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
        $couponField => $couponValue === 'id' ? $coupon->id : $couponValue,
    ];

    withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), $payload);

    $order = Order::query()->sole();
    expect($order->discount_amount->dollars())->toBe($expectedDiscount)
        ->and($order->total->dollars())->toBe(40.00 - $expectedDiscount)
        ->and($order->coupon_id)->toBe($expectedDiscount > 0 ? $coupon->id : null)
        ->and($coupon->refresh()->used_count)->toBe($expectedUses);
})->with([
    'numeric coupon id without the code' => ['coupon_id', 'id', 0.0, 0],
    'correct coupon code' => ['coupon_code', ' back-private ', 5.0, 1],
    'wrong coupon code' => ['coupon_code', 'BACK-WRONG', 0.0, 0],
]);

/**
 * Turns pickup slots on (08:00-10:00, 30 minutes, 2 per slot) for the weekday three days out.
 *
 * @return array<string, mixed> the order payload for a pickup on that day
 */
function pickupSlotOrderPayload(Product $product, ?string $time): array
{
    app()->instance(TenantSettings::class, makeTenantSettings(
        orders: makeOrderSettings([
            'pickupSlotsEnabled' => true,
            'pickupSlotIntervalMinutes' => 30,
            'pickupSlotMaxPerWindow' => 2,
        ]),
        store: makeStoreInfo(['name' => 'Test']),
        onboarding: new OnboardingSettings(completedAt: now()->toDateTimeString()),
    ));
    $date = now()->addDays(3);
    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => true,
        'open_time' => '08:00',
        'close_time' => '10:00',
    ]);

    return [
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@example.com',
        'delivery_type' => 'pickup',
        'delivery_date' => $date->toDateString(),
        'delivery_time' => $time,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ];
}

test('storefront pickup orders for a full slot get a form error and no order is created', function () {
    $product = Product::factory()->create();
    $payload = pickupSlotOrderPayload($product, '08:30');
    Order::factory()->confirmed()->count(2)->create([
        'delivery_date' => $payload['delivery_date'],
        'delivery_time' => '08:30',
        'delivery_type' => DeliveryType::Pickup->value,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), $payload);

    $response->assertSessionHasErrors(['delivery_time' => 'That pickup time is no longer available. Please choose another.'])
        ->assertSessionHasInput('delivery_time', '08:30');
    expect(Order::query()->count())->toBe(2);
});

test('storefront pickup orders require a time when slots are enabled', function () {
    $payload = pickupSlotOrderPayload(Product::factory()->create(), null);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), $payload);

    $response->assertSessionHasErrors(['delivery_time']);
    expect(Order::query()->count())->toBe(0);
});

test('storefront pickup orders for an open slot are placed', function () {
    $payload = pickupSlotOrderPayload(Product::factory()->create(), '08:30');

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), $payload);

    $response->assertSessionHasNoErrors();
    expect(Order::query()->sole()->delivery_time->format('H:i'))->toBe('08:30');
});

test('a slot that fills while the order is being placed becomes a form error', function () {
    $createOrder = Double::for(CreateOrder::class);
    $createOrder->expects('__invoke')->throws(new PickupSlotUnavailableException('2026-05-04', '08:30'));
    app()->instance(CreateOrder::class, $createOrder);
    $payload = pickupSlotOrderPayload(Product::factory()->create(), '08:30');

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), $payload);

    $response->assertRedirect()
        ->assertSessionHasErrors(['delivery_time' => 'That pickup time is no longer available. Please choose another.'])
        ->assertSessionHasInput('delivery_time', '08:30');
});

test('an invalid order asked for as JSON returns a 422 with the errors keyed by field', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('order.store', [], false), [
            'items' => [
                ['product_id' => 0, 'quantity' => 1],
            ],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'customer_name',
            'customer_email',
            'delivery_type',
            'delivery_date',
            'items.0.product_id',
        ]);
});

test('a full pickup slot asked for as JSON returns the slot message under delivery_time', function () {
    $product = Product::factory()->create();
    $payload = pickupSlotOrderPayload($product, '08:30');
    Order::factory()->confirmed()->count(2)->create([
        'delivery_date' => $payload['delivery_date'],
        'delivery_time' => '08:30',
        'delivery_type' => DeliveryType::Pickup->value,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('order.store', [], false), $payload);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['delivery_time' => 'That pickup time is no longer available. Please choose another.']);
});

dataset('order domain failures', [
    'below the minimum order amount' => [
        fn () => new MinimumOrderAmountNotMetException(deliveryType: 'pickup', subtotal: 5.00, minimum: 15.00),
        'items',
        'Minimum pickup order is $15.00. Please add more items to continue.',
    ],
    'not enough stock' => [
        fn () => new InsufficientStockException(['Flour', 'Butter']),
        'items',
        'Sorry, we don\'t have enough Flour, Butter in stock right now. Please reduce the quantity or remove an item.',
    ],
    'no orderable items left in the cart' => [
        fn () => new NoOrderableItemsException,
        'items',
        'Some items in your cart are no longer available. Please review your cart.',
    ],
    'date fully booked' => [
        fn () => null,
        'delivery_date',
        'Sorry, this date is fully booked. Please choose another date.',
    ],
    'pickup slot taken while ordering' => [
        fn () => new PickupSlotUnavailableException('2026-05-04', '08:30'),
        'delivery_time',
        'That pickup time is no longer available. Please choose another.',
    ],
]);

/**
 * Makes the order pipeline fail the way the given outcome says: an exception is thrown, null is a cancelled order.
 */
function failOrderPipeline(Closure $outcome): void
{
    $failure = $outcome();
    $createOrder = Double::for(CreateOrder::class);
    $expectation = $createOrder->expects('__invoke');
    $failure === null ? $expectation->returns(null) : $expectation->throws($failure);
    app()->instance(CreateOrder::class, $createOrder);
}

/**
 * @return array<string, mixed>
 */
function domainFailureOrderPayload(): array
{
    return [
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@example.com',
        'delivery_type' => 'pickup',
        'delivery_date' => now()->addDays(2)->toDateString(),
        'items' => [
            ['product_id' => Product::factory()->create()->id, 'quantity' => 1],
        ],
    ];
}

test('an order the pipeline rejects, asked for as JSON, returns a 422 with the message under its field', function (Closure $outcome, string $field, string $message) {
    failOrderPipeline($outcome);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('order.store', [], false), domainFailureOrderPayload());

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([$field => $message])
        ->assertJsonPath('message', $message);
})->with('order domain failures');

test('an order the pipeline rejects, submitted as a form, redirects back with the error and the input', function (Closure $outcome, string $field, string $message) {
    failOrderPipeline($outcome);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.store', [], false), domainFailureOrderPayload());

    $response->assertRedirect()
        ->assertSessionHasErrors([$field => $message])
        ->assertSessionHasInput('customer_name', 'Jane Doe');
})->with('order domain failures');

test('a cart whose only product was deactivated is told its items are unavailable, not that the date is full', function (string $method) {
    $payload = domainFailureOrderPayload();
    Product::query()->update(['is_active' => false]);

    $response = withoutMiddleware(tenantMiddleware())
        ->{$method}(route('order.store', [], false), $payload);

    $message = 'Some items in your cart are no longer available. Please review your cart.';
    match ($method) {
        'postJson' => $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items' => $message])
            ->assertJsonMissingValidationErrors(['delivery_date']),
        default => $response->assertRedirect()
            ->assertSessionHasErrors(['items' => $message])
            ->assertSessionDoesntHaveErrors(['delivery_date'])
            ->assertSessionHasInput('customer_name', 'Jane Doe'),
    };
    expect(Order::query()->count())->toBe(0);
})->with([
    'JSON' => ['postJson'],
    'form post' => ['post'],
]);
