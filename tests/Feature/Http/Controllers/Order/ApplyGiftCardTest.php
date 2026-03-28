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
        ->postJson('/gift-card/apply', [
            'code' => 'GIFT-TEST-0001',
            'subtotal' => 30.00,
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('applicable_amount', 30);
});
