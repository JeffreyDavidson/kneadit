<?php

use App\Services\Settings\TenantSettings;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('menu controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/menu');

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('heroEyebrow')
        ->assertViewHas('ctaDesc');
});

test('gallery controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/gallery');

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('reviews controller passes settings, content, and stats to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/reviews');

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('vm')
        ->assertViewHas('featured');
});

test('gift cards controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/gift-cards');

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('loyalty controller show passes vm to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/rewards');

    $response->assertOk()
        ->assertViewHas('vm');
});

test('catering controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/catering');

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('submit review controller passes settings to view', function () {
    $order = App\Models\Orders\Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get("/review/{$order->order_number}");

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('ratingDescriptions');
});

test('survey controller passes settings and content to view', function () {
    $survey = App\Models\Engagement\Survey::factory()->create(['is_active' => true]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get("/survey/{$survey->id}");

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('about controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/about');

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('contact controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/contact');

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('order tracking controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/track');

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('order controller index passes settings to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/order');

    $response->assertOk()
        ->assertViewHas('settings', fn (TenantSettings $s) => is_int($s->leadTimeHours) && is_bool($s->deliveryEnabled));
});

test('order confirmation controller passes settings and content to view', function () {
    $order = App\Models\Orders\Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get("/order/confirmation/{$order->order_number}");

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('journeySteps');
});
