<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('menu controller passes heroImageUrl and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/menu');

    $response->assertOk()
        ->assertViewHas('heroImageUrl')
        ->assertViewHas('content')
        ->assertViewHas('heroEyebrow')
        ->assertViewHas('ctaDesc');
});

test('gallery controller passes heroImageUrl and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/gallery');

    $response->assertOk()
        ->assertViewHas('storeName')
        ->assertViewHas('heroImageUrl')
        ->assertViewHas('content');
});

test('reviews controller passes heroImageUrl, content, and stats to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/reviews');

    $response->assertOk()
        ->assertViewHas('storeName')
        ->assertViewHas('heroImageUrl')
        ->assertViewHas('content')
        ->assertViewHas('fiveStarCount')
        ->assertViewHas('fiveStarPct')
        ->assertViewHas('featured');
});

test('gift cards controller passes heroImageUrl and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/gift-cards');

    $response->assertOk()
        ->assertViewHas('heroImageUrl')
        ->assertViewHas('content');
});

test('loyalty controller show passes heroImageUrl and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/rewards');

    $response->assertOk()
        ->assertViewHas('heroImageUrl')
        ->assertViewHas('content');
});

test('catering controller passes heroImageUrl and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/catering');

    $response->assertOk()
        ->assertViewHas('storeName')
        ->assertViewHas('heroImageUrl')
        ->assertViewHas('content');
});

test('submit review controller passes heroImageUrl to view', function () {
    $order = App\Models\Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get("/review/{$order->order_number}");

    $response->assertOk()
        ->assertViewHas('heroImageUrl')
        ->assertViewHas('content')
        ->assertViewHas('ratingDescriptions');
});

test('survey controller passes heroImageUrl and content to view', function () {
    $survey = App\Models\Survey::factory()->create(['is_active' => true]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get("/survey/{$survey->id}");

    $response->assertOk()
        ->assertViewHas('storeName')
        ->assertViewHas('heroImageUrl')
        ->assertViewHas('content');
});

test('about controller passes heroImageUrl and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/about');

    $response->assertOk()
        ->assertViewHas('storeName')
        ->assertViewHas('heroImageUrl')
        ->assertViewHas('content');
});

test('contact controller passes heroImageUrl and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/contact');

    $response->assertOk()
        ->assertViewHas('storeName')
        ->assertViewHas('heroImageUrl')
        ->assertViewHas('content');
});

test('order tracking controller passes heroImageUrl and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/track');

    $response->assertOk()
        ->assertViewHas('content')
        ->assertViewHas('storeName')
        ->assertViewHas('heroImageUrl');
});

test('order controller index passes computed settings to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/order');

    $response->assertOk()
        ->assertViewHas('leadTimeHours')
        ->assertViewHas('leadTimeDays')
        ->assertViewHas('deliveryEnabled')
        ->assertViewHas('deliveryTiers')
        ->assertViewHas('allergyDisclaimer')
        ->assertViewHas('paymentMethods')
        ->assertViewHas('freeDeliveryMin');
});

test('order confirmation controller passes heroImageUrl and content to view', function () {
    $order = App\Models\Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get("/order/confirmation/{$order->order_number}");

    $response->assertOk()
        ->assertViewHas('heroImageUrl')
        ->assertViewHas('content')
        ->assertViewHas('journeySteps');
});
