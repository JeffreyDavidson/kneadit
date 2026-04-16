<?php

use App\Actions\Orders\CreateOrder;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use App\Services\Stripe\StripeCheckoutService;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();

    // Prevent StripeCheckoutService from hitting real Stripe API
    config(['cashier.secret' => 'sk_test_fake']);

    // Bind TenantSettings with a 1-day lead time so delivery_date validation passes
    app()->instance(TenantSettings::class, new TenantSettings(
        storeName: 'Test',
        storeEmail: null,
        storePhone: null,
        storeAddress: null,
        storeWebsite: null,
        storeLogo: null,
        storeTagline: null,
        brandColorPrimary: '#d4920c',
        onboardingCompletedAt: now()->toDateTimeString(),
        storefrontTheme: 'classic',
        businessTagline: null,
        aboutUsText: null,
        heroImage: null,
        heroStyle: 'split',
        allergyDisclaimer: null,
        cateringHeroImage: null,
        loyaltyHeroImage: null,
        giftCardsHeroImage: null,
        leadTimeHours: 24,
        deliveryEnabled: true,
        freeDeliveryMinimum: '50',
        deliveryFeeTiers: [],
        paymentMethodsAccepted: [],
        operatingHours: [],
        faqItems: [],
        loyaltyProgramName: 'Rewards',
        loyaltyPointsPerDollar: '10',
        loyaltyEnabled: true,
        cateringMinimumGuests: '10',
        cateringLeadTimeDays: '14',
        socialMediaLinks: [],
        homepageSections: [],
        cateringEnabled: false,
        storePhoto: null,
        announcementEnabled: false,
        announcementText: '',
        announcementType: 'info',
        showPolicies: false,
        cancellationPolicy: '',
        depositPolicy: '',
        refundPolicy: '',
        pickupPolicy: '',
        additionalTerms: '',
        birthdayProgramEnabled: false,
        birthdayCouponEnabled: false,
        birthdayDiscountPercentage: 15,
        birthdayCouponValidDays: 7,
        reviewRequestsEnabled: false,
        reviewRequestDelayHours: 24,
        repeatRemindersEnabled: false,
        repeatReminderDays: 30,
        giftCardPresetAmounts: [10, 25, 50, 100],
        giftCardDefaultAmount: 25,
    ));
});

test('successful order creation redirects to confirmation', function () {
    $order = Order::factory()->create();

    $createOrder = Mockery::mock(CreateOrder::class);
    $createOrder->shouldReceive('__invoke')->once()->andReturn($order);
    app()->instance(CreateOrder::class, $createOrder);

    $stripeService = Mockery::mock(StripeCheckoutService::class);
    $stripeService->shouldReceive('redirectToCheckout')->once()->andReturnNull();
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
    $createOrder = Mockery::mock(CreateOrder::class);
    $createOrder->shouldReceive('__invoke')->once()->andReturnNull();
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

    $createOrder = Mockery::mock(CreateOrder::class);
    $createOrder->shouldReceive('__invoke')->once()->andReturn($order);
    app()->instance(CreateOrder::class, $createOrder);

    $stripeService = Mockery::mock(StripeCheckoutService::class);
    $stripeService->shouldReceive('redirectToCheckout')
        ->once()
        ->andReturn('https://checkout.stripe.com/pay/cs_test_abc123');
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
