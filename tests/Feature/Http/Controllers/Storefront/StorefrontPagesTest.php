<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('storefront about page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/about');

    $response->assertOk();
});

test('storefront home page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/');

    $response->assertOk();
});

test('storefront menu page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/menu');

    $response->assertOk();
});

test('storefront gallery page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/gallery');

    $response->assertOk();
});

test('storefront contact page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/contact');

    $response->assertOk();
});

test('storefront reviews page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/reviews');

    $response->assertOk();
});

test('storefront gift cards page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/gift-cards');

    $response->assertOk();
});

test('storefront catering page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/catering');

    $response->assertOk();
});

test('storefront rewards page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/rewards');

    $response->assertOk();
});

test('storefront survey page renders', function () {
    $survey = App\Models\Engagement\Survey::factory()->create(['is_active' => true]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get("/survey/{$survey->id}");

    $response->assertOk();
});

test('storefront review submission page renders', function () {
    $order = App\Models\Orders\Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get("/review/{$order->order_number}");

    $response->assertOk();
});
