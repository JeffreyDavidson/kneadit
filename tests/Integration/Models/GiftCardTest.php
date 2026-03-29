<?php

use App\Models\GiftCard;
use App\Services\GiftCardService;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
});

test('gift cards page loads', function () {
    $response = withoutMiddleware(tenantMiddleware())->get('/gift-cards');

    $response->assertOk();
});

test('gift card can be purchased with valid data', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/gift-cards/purchase', [
            'purchaser_name' => 'John Doe',
            'purchaser_email' => 'john@example.com',
            'recipient_name' => 'Jane Doe',
            'recipient_email' => 'jane@example.com',
            'message' => 'Happy Birthday!',
            'initial_balance' => 50.00,
        ]);

    $response->assertOk();
    $response->assertJsonStructure(['success', 'gift_card' => ['code', 'balance']]);
    $this->assertDatabaseHas('gift_cards', [
        'purchaser_email' => 'john@example.com',
        'initial_balance' => 50.00,
    ]);
});

test('gift card balance check works', function () {
    $service = new GiftCardService;
    $card = $service->create([
        'purchaser_name' => 'John',
        'purchaser_email' => 'john@example.com',
        'initial_balance' => 25.00,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/gift-cards/balance', [
            'code' => $card->code,
        ]);

    $response->assertOk();
    $response->assertJson(['success' => true, 'current_balance' => 25.00]);
});

test('gift card balance check returns 404 for invalid code', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/gift-cards/balance', [
            'code' => 'INVALID-CODE-HERE',
        ]);

    $response->assertNotFound();
    $response->assertJson(['success' => false]);
});

test('gift card code is generated in correct format', function () {
    $service = new GiftCardService;
    $code = $service->generateCode();

    expect($code)->toMatch('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/');
});

test('gift card redemption deducts balance', function () {
    $service = new GiftCardService;
    $card = $service->create([
        'purchaser_name' => 'John',
        'purchaser_email' => 'john@example.com',
        'initial_balance' => 100.00,
    ]);

    $result = $service->redeem($card->code, 30.00);

    expect($result['success'])->toBeTrue()->and($result)->toMatchArray(['amount_applied' => 30.00, 'remaining_balance' => 70.00]);
});

test('depleted gift card cannot be redeemed', function () {
    $service = new GiftCardService;
    $card = $service->create([
        'purchaser_name' => 'John',
        'purchaser_email' => 'john@example.com',
        'initial_balance' => 10.00,
    ]);

    $service->redeem($card->code, 10.00);

    $result = $service->redeem($card->code, 5.00);

    expect($result['success'])->toBeFalse();
});

test('expired gift card cannot be redeemed', function () {
    $card = GiftCard::query()->create([
        'code' => 'EXPD-TEST-CODE-1234',
        'initial_balance' => 50.00,
        'current_balance' => 50.00,
        'purchaser_name' => 'John',
        'purchaser_email' => 'john@example.com',
        'is_active' => true,
        'expires_at' => now()->subDay(),
    ]);

    $service = new GiftCardService;
    $result = $service->redeem($card->code, 10.00);

    expect($result['success'])->toBeFalse();
});

test('gift card purchase validates required fields', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/gift-cards/purchase', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['purchaser_name', 'purchaser_email', 'initial_balance']);
});
