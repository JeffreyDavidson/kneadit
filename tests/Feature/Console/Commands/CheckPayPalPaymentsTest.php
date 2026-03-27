<?php

beforeEach(fn () => setUpCentralTest());

test('paypal check-payments command runs successfully with no tenants', function () {
    $this->artisan('paypal:check-payments')
        ->assertSuccessful();
});
