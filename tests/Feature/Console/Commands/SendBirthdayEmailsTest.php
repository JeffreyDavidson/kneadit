<?php

use Illuminate\Support\Facades\Mail;

beforeEach(fn () => setUpCentralTest());

test('birthday emails command runs successfully with no tenants', function () {
    Mail::fake();

    pendingArtisan('birthday:send-emails')
        ->assertSuccessful();

    Mail::assertNothingSent();
});
