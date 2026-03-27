<?php

use Illuminate\Support\Facades\Mail;

beforeEach(fn () => setUpCentralTest());

test('checkins:send command runs successfully with no tenants', function () {
    Mail::fake();

    $this->artisan('checkins:send')
        ->assertSuccessful();

    Mail::assertNothingSent();
});
