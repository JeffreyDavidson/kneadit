<?php

use Illuminate\Support\Facades\Mail;

beforeEach(fn () => setUpCentralTest());

test('orders:send-repeat-reminders command runs successfully with no tenants', function () {
    Mail::fake();

    $this->artisan('orders:send-repeat-reminders')
        ->assertSuccessful();

    Mail::assertNothingSent();
});
