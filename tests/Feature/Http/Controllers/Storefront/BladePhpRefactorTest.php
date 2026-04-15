<?php

use App\Services\Settings\TenantSettings;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('storefront home page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/');

    $response->assertOk();
});

test('menu controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.menu', [], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('heroEyebrow')
        ->assertViewHas('ctaDesc');
});

test('gallery controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.gallery', [], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('reviews controller passes settings, content, and stats to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.reviews', [], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('vm')
        ->assertViewHas('featured');
});

test('gift cards controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.giftCards', [], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('loyalty controller show passes vm to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.rewards', [], false));

    $response->assertOk()
        ->assertViewHas('vm');
});

test('catering controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.catering', [], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('submit review controller passes settings to view', function () {
    $order = App\Models\Orders\Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.submitReview', $order->order_number, false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('ratingDescriptions');
});

test('survey controller passes settings and content to view', function () {
    $survey = App\Models\Engagement\Survey::factory()->create(['is_active' => true]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.survey', $survey, false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('about controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.about', [], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('contact controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('contact.show', [], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('order tracking controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.track', [], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('order controller index passes settings to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertViewHas('settings', fn (TenantSettings $s) => is_int($s->leadTimeHours) && is_bool($s->deliveryEnabled));
});

test('order confirmation controller passes settings and content to view', function () {
    $order = App\Models\Orders\Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('journeySteps');
});
