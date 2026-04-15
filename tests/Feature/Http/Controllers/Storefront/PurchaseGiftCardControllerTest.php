<?php

use App\Models\Financial\GiftCard;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('can purchase a gift card', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('giftCards.purchase', [], false), [
            'purchaser_name' => 'Jane Doe',
            'purchaser_email' => 'jane@example.com',
            'initial_balance' => 25.00,
        ]);

    $response->assertOk()
        ->assertJsonStructure(['data' => ['code', 'balance']]);

    expect(GiftCard::query()->count())->toBe(1);
});

test('purchase gift card validates required fields', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('giftCards.purchase', [], false), []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['purchaser_name', 'purchaser_email', 'initial_balance']);
});
