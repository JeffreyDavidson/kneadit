<?php

use App\Models\Financial\Coupon;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('API validates a valid coupon', function () {
    Coupon::factory()->percentage()->create([
        'code' => 'API10OFF',
        'value' => 10,
        'is_active' => true,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/coupon/validate', [
            'code' => 'API10OFF',
            'subtotal' => 50.00,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.valid', true);
});

test('API returns error status for invalid coupon', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/coupon/validate', [
            'code' => 'NONEXISTENT',
            'subtotal' => 50.00,
        ]);

    $response->assertUnprocessable()
        ->assertJsonPath('data', null);
});

test('API returns error status for expired coupon', function () {
    Coupon::factory()->expired()->create([
        'code' => 'EXPIRED10',
        'value' => 10,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/coupon/validate', [
            'code' => 'EXPIRED10',
            'subtotal' => 50.00,
        ]);

    $response->assertUnprocessable()
        ->assertJsonPath('data', null);
});
