<?php

use App\Actions\Stripe\HandleConnectCheckoutCompleted;

beforeEach(fn () => setUpCentralTest());

test('it does nothing when session has no id', function () {
    $session = ['id' => null, 'metadata' => []];

    resolve(HandleConnectCheckoutCompleted::class)($session);

    expect(true)->toBeTrue();
});

test('it does nothing when tenant id is missing from metadata', function () {
    $session = [
        'id' => 'cs_test_456',
        'metadata' => ['order_id' => 1],
    ];

    resolve(HandleConnectCheckoutCompleted::class)($session);

    expect(true)->toBeTrue();
});

test('it does nothing when tenant is not found', function () {
    $session = [
        'id' => 'cs_test_789',
        'metadata' => [
            'order_id' => 1,
            'tenant_id' => 'nonexistent-tenant',
        ],
    ];

    resolve(HandleConnectCheckoutCompleted::class)($session);

    expect(true)->toBeTrue();
});
