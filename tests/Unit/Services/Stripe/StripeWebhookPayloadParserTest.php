<?php

use App\Services\Stripe\StripeWebhookPayloadParser;

test('extracts the normalized stripe object from a webhook payload', function () {
    expect(resolve(StripeWebhookPayloadParser::class)->object([
        'data' => ['object' => ['customer' => 'cus_test']],
    ]))->toBe(['customer' => 'cus_test']);
});

test('normalizes non-empty strings and rejects other values', function () {
    $parser = resolve(StripeWebhookPayloadParser::class);

    expect($parser->stringValue('cus_test'))->toBe('cus_test')
        ->and($parser->stringValue(''))->toBeNull()
        ->and($parser->stringValue(123))->toBeNull();
});

test('builds the stripe price map from configured prices', function () {
    config(['kneadit.stripe_prices' => [
        'starter' => 'price_starter',
        'growth' => 'price_growth',
    ]]);

    expect(resolve(StripeWebhookPayloadParser::class)->priceMap())->toBe([
        'price_starter' => 'starter',
        'price_growth' => 'growth',
    ]);
});
