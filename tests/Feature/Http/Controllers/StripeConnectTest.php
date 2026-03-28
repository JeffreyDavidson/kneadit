<?php

use App\Http\Controllers\StripeConnectController;

beforeEach(fn () => setUpTenantTest());

test('getAccountStatus returns null when no connect id configured', function () {
    expect(StripeConnectController::getAccountStatus())->toBeNull();
});
