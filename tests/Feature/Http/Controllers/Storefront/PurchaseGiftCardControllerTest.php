<?php

use App\Models\Financial\GiftCard;
use App\Models\Platform\Setting;
use App\Services\Settings\SettingsManager;

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

test('purchase success message can be customized via page content', function () {
    Setting::factory()->create([
        'key' => 'page_content',
        'value' => json_encode([
            'gift_cards' => ['flash_purchased' => 'Gift card on its way!'],
        ]),
    ]);
    resolve(SettingsManager::class)->flushCache();

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('giftCards.purchase', [], false), [
            'purchaser_name' => 'Jane Doe',
            'purchaser_email' => 'jane@example.com',
            'initial_balance' => 25.00,
        ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Gift card on its way!');
});

test('purchase gift card validates required fields', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('giftCards.purchase', [], false), []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['purchaser_name', 'purchaser_email', 'initial_balance']);
});
