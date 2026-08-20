<?php

use App\Actions\Orders\CreateOrder;
use App\DataTransferObjects\Settings\OnboardingSettings;
use App\Exceptions\Orders\MinimumOrderAmountNotMetException;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Platform\Setting;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use App\Services\Stripe\StripeCheckoutService;

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

    $createOrder = JMac\Testing\Double::for(CreateOrder::class);
    $createOrder->expects('__invoke')->returns($order);
    app()->instance(CreateOrder::class, $createOrder);

    $stripeService = JMac\Testing\Double::for(StripeCheckoutService::class);
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
    $createOrder = JMac\Testing\Double::for(CreateOrder::class);
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

    $createOrder = JMac\Testing\Double::for(CreateOrder::class);
    $createOrder->expects('__invoke')->returns($order);
    app()->instance(CreateOrder::class, $createOrder);

    $stripeService = JMac\Testing\Double::for(StripeCheckoutService::class);
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

test('returns error when order subtotal is below minimum', function () {
    $createOrder = JMac\Testing\Double::for(CreateOrder::class);
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
        ->assertSessionHasErrors(['items' => 'Minimum pickup order is $15.00']);
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

    $createOrder = JMac\Testing\Double::for(CreateOrder::class);
    $createOrder->expects('__invoke')->returns($order);
    app()->instance(CreateOrder::class, $createOrder);

    $stripeService = JMac\Testing\Double::for(StripeCheckoutService::class);
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

    $createOrder = JMac\Testing\Double::for(CreateOrder::class);
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
