<?php

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('apply valid coupon returns discount', function () {
    $coupon = Coupon::factory()->percentage()->create([
        'code' => 'SAVE10',
        'value' => 10,
        'is_active' => true,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('coupon.apply', [], false), [
            'code' => 'SAVE10',
            'subtotal' => 50.00,
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'coupon_id' => $coupon->id,
            'code' => 'SAVE10',
        ]);
});

test('apply invalid coupon code returns error', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('coupon.apply', [], false), [
            'code' => 'NONEXISTENT',
            'subtotal' => 50.00,
        ]);

    $response->assertUnprocessable()
        ->assertJsonStructure(['error']);
});

test('apply expired coupon returns error', function () {
    Coupon::factory()->expired()->create(['code' => 'OLDCODE']);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('coupon.apply', [], false), [
            'code' => 'OLDCODE',
            'subtotal' => 50.00,
        ]);

    $response->assertUnprocessable()
        ->assertJsonStructure(['error']);
});

test('apply coupon validates required fields', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('coupon.apply', [], false), []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['code', 'subtotal']);
});
