<?php

use App\Models\Coupon;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('apply coupon returns discount for valid coupon', function () {
    Coupon::factory()->percentage()->create(['code' => 'SAVE10', 'value' => 10]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/coupon/apply', [
            'code' => 'SAVE10',
            'subtotal' => 50.00,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.code', 'SAVE10');
});

test('apply coupon returns error for invalid code', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/coupon/apply', [
            'code' => 'FAKECODE',
            'subtotal' => 50.00,
        ]);

    $response->assertUnprocessable()
        ->assertJsonPath('message', 'Coupon not found.');
});
