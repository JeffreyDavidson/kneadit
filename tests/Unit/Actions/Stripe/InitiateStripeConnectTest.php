<?php

use App\Actions\Stripe\InitiateStripeConnect;

test('it can be instantiated', function () {
    expect(class_exists(InitiateStripeConnect::class))->toBeTrue();
});
