<?php

use App\Models\GiftCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('can purchase a gift card', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('gift-cards.purchase', [], false), [
            'purchaser_name' => 'Jane Doe',
            'purchaser_email' => 'jane@example.com',
            'initial_balance' => 25.00,
        ]);

    $response->assertOk()
        ->assertJson(['success' => true])
        ->assertJsonStructure(['gift_card' => ['code', 'balance']]);

    expect(GiftCard::query()->count())->toBe(1);
});

test('purchase gift card validates required fields', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('gift-cards.purchase', [], false), []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['purchaser_name', 'purchaser_email', 'initial_balance']);
});
