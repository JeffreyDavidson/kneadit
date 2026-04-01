<?php

use App\Models\GiftCard;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('apply gift card returns balance for valid card', function () {
    GiftCard::factory()->create([
        'code' => 'GIFT-TEST-0001',
        'current_balance' => 50.00,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('gift-card.apply', absolute: false), [
            'code' => 'GIFT-TEST-0001',
            'subtotal' => 30.00,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.applicable_amount', 30);
});

test('apply gift card returns error for unknown code', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('gift-card.apply', absolute: false), [
            'code' => 'NONEXISTENT',
            'subtotal' => 30.00,
        ]);

    $response->assertUnprocessable()
        ->assertJsonPath('message', 'Gift card not found.');
});
