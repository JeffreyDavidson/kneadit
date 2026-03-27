<?php

use Illuminate\Support\Facades\Mail;

beforeEach(fn () => setUpCentralTest());

test('digest:weekly command runs successfully with no tenants', function () {
    Mail::fake();

    $this->artisan('digest:weekly')
        ->assertSuccessful();

    Mail::assertNothingSent();
});
