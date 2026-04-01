<?php

use App\Models\Financial\GiftCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('check balance returns card details for valid code', function () {
    $card = GiftCard::factory()->create([
        'code' => 'BAL-CHECK-1234',
        'current_balance' => 50.00,
        'is_active' => true,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('gift-cards.balance', [], false), [
            'code' => 'BAL-CHECK-1234',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.is_usable', true);
});

test('check balance returns 404 for invalid code', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('gift-cards.balance', [], false), [
            'code' => 'INVALID-CODE',
        ]);

    $response->assertNotFound()
        ->assertJsonPath('message', 'Gift card not found.');
});
