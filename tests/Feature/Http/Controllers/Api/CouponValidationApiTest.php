<?php

use App\Models\Coupon;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('coupon validation returns valid for active coupon', function () {
    Coupon::factory()->percentage()->create(['code' => 'SPRING20', 'value' => 20]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/coupon/validate', [
            'code' => 'SPRING20',
            'subtotal' => 100.00,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.valid', true)
        ->assertJsonPath('data.discount_amount', 20);
});

test('coupon validation returns invalid for expired coupon', function () {
    Coupon::factory()->expired()->create(['code' => 'OLDCODE']);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/coupon/validate', [
            'code' => 'OLDCODE',
            'subtotal' => 50.00,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.valid', false);
});
