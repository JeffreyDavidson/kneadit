<?php

use App\Enums\Financial\StripeConnectStatus;
use App\Services\Stripe\StripeSettingsReader;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('connect status reflects the stored connect account and charges flag', function (array $stored, StripeConnectStatus $expected) {
    settings($stored);

    expect(resolve(StripeSettingsReader::class)->connectStatus())->toBe($expected);
})->with([
    'no account' => [[], StripeConnectStatus::NotConnected],
    'empty account id' => [['stripe_connect_id' => ''], StripeConnectStatus::NotConnected],
    'charges not enabled' => [
        ['stripe_connect_id' => 'acct_test', 'stripe_connect_charges_enabled' => '0'],
        StripeConnectStatus::ChargesPending,
    ],
    'charges flag missing' => [['stripe_connect_id' => 'acct_test'], StripeConnectStatus::ChargesPending],
    'charges enabled' => [
        ['stripe_connect_id' => 'acct_test', 'stripe_connect_charges_enabled' => '1'],
        StripeConnectStatus::ChargesEnabled,
    ],
]);
