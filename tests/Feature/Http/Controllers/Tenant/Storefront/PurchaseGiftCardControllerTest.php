<?php

use App\Models\Financial\GiftCard;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

// Online gift card purchases are turned off until a paid checkout exists.
test('the storefront no longer offers an online gift card purchase endpoint', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/gift-cards/purchase', [
            'purchaser_name' => 'Jane Doe',
            'purchaser_email' => 'jane@example.com',
            'initial_balance' => 500,
        ]);

    expect($response->status())->toBeIn([404, 405])
        ->and(GiftCard::query()->count())->toBe(0);
});

test('the gift cards page has no online purchase form', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.giftCards', absolute: false));

    $response->assertOk()
        ->assertDontSeeHtml('data-test="gift-card-purchase-form"')
        ->assertSeeHtml('data-test="gift-card-in-store-notice"')
        ->assertSeeHtml('data-test="gift-card-balance-form"');
});
