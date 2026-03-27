<?php

use Illuminate\Support\Facades\Mail;

beforeEach(fn () => setUpCentralTest());

test('birthday:send-discounts command runs successfully with no tenants', function () {
    Mail::fake();

    $this->artisan('birthday:send-discounts')
        ->assertSuccessful();

    Mail::assertNothingSent();
});
