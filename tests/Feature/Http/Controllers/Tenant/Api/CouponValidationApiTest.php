<?php

use App\Models\Financial\Coupon;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('API validates a valid coupon and returns JSON:API envelope', function () {
    Coupon::factory()->percentage()->create([
        'code' => 'API10OFF',
        'percentage' => 10,
        'is_active' => true,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/coupon/validate', [
            'code' => 'API10OFF',
            'subtotal' => 50.00,
        ]);

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonPath('data.type', 'coupon-validations')
        ->assertJsonPath('data.id', 'API10OFF')
        ->assertJsonPath('data.attributes.valid', true);
});

test('API returns a JSON:API validation error for an unknown coupon', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/coupon/validate', [
            'code' => 'NONEXISTENT',
            'subtotal' => 50.00,
        ]);

    $response->assertUnprocessable()
        ->assertJsonPath('errors.0.source.pointer', '/data/attributes/code');
});

test('API returns a JSON:API validation error for an expired coupon', function () {
    Coupon::factory()->expired()->create([
        'code' => 'EXPIRED10',
        'percentage' => 10,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/coupon/validate', [
            'code' => 'EXPIRED10',
            'subtotal' => 50.00,
        ]);

    $response->assertUnprocessable()
        ->assertJsonPath('errors.0.source.pointer', '/data/attributes/code');
});
