<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    setUpCentralTest();
    Mail::fake();
    @mkdir(storage_path('logs'), 0755, true);
});

test('health check command runs and checks database', function () {
    Http::fake(['*' => Http::response('OK', 200)]);
    Http::preventStrayRequests();

    $this->artisan('health:check')
        ->expectsOutputToContain('Database connection OK');
});
