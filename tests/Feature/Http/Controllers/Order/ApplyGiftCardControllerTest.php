<?php

use App\Models\GiftCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('apply valid gift card returns balance', function () {
    $card = GiftCard::factory()->create([
        'code' => 'GIFT-TEST-1234',
        'current_balance' => 25.00,
        'is_active' => true,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('gift-card.apply', [], false), [
            'code' => 'GIFT-TEST-1234',
            'subtotal' => 50.00,
        ]);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'gift_card_id' => $card->id,
                'available_balance' => 25.00,
                'applicable_amount' => 25.00,
            ],
        ]);
});

test('apply nonexistent gift card returns error', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('gift-card.apply', [], false), [
            'code' => 'INVALID-CODE',
            'subtotal' => 50.00,
        ]);

    $response->assertUnprocessable()
        ->assertJson(['message' => 'Gift card not found.']);
});

test('gift card caps applicable amount at subtotal', function () {
    GiftCard::factory()->create([
        'code' => 'GIFT-BIG-BALANCE',
        'current_balance' => 100.00,
        'is_active' => true,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('gift-card.apply', [], false), [
            'code' => 'GIFT-BIG-BALANCE',
            'subtotal' => 30.00,
        ]);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'available_balance' => 100.00,
                'applicable_amount' => 30.00,
            ],
        ]);
});
