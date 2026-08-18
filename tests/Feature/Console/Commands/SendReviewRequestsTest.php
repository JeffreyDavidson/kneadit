<?php

use Illuminate\Support\Facades\Mail;

beforeEach(fn () => setUpCentralTest());

test('review requests command runs successfully with no tenants', function () {
    Mail::fake();

    $this->artisan('reviews:send-requests')
        ->assertSuccessful();

    Mail::assertNothingSent();
});
