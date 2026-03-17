<?php

use Illuminate\Support\Facades\Http;

beforeEach(function () {
    setUpCentralTest();
});

test('health check command exists', function () {
    Http::fake(['*' => Http::response('OK', 200)]);

    $this->artisan('health:check')
        ->assertSuccessful();
});

test('health check verifies database connection', function () {
    Http::fake(['*' => Http::response('OK', 200)]);

    $this->artisan('health:check')
        ->expectsOutputToContain('Database connection OK')
        ->assertSuccessful();
});

test('health check verifies users table', function () {
    Http::fake(['*' => Http::response('OK', 200)]);

    $this->artisan('health:check')
        ->expectsOutputToContain('Users table OK')
        ->assertSuccessful();
});

test('health check verifies disk space', function () {
    Http::fake(['*' => Http::response('OK', 200)]);

    $this->artisan('health:check')
        ->expectsOutputToContain('Disk space OK')
        ->assertSuccessful();
});

test('health check verifies storage writable', function () {
    Http::fake(['*' => Http::response('OK', 200)]);

    $this->artisan('health:check')
        ->expectsOutputToContain('Storage/logs writable')
        ->assertSuccessful();
});

test('health check detects homepage failure', function () {
    Http::fake(['*' => Http::response('Server Error', 500)]);

    $this->artisan('health:check')
        ->assertFailed();
});
