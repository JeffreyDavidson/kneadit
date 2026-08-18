<?php

use Illuminate\Support\Facades\Mail;

beforeEach(fn () => setUpCentralTest());

test('birthday emails command runs successfully with no tenants', function () {
    Mail::fake();

    $this->artisan('birthday:send-emails')
        ->assertSuccessful();

    Mail::assertNothingSent();
});
