<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
    config(['mail.platform_notify' => 'test@example.com']);

    // Point storage to a real writable temp directory so the health check passes
    $tempStorage = sys_get_temp_dir() . '/kneadit_test_storage_' . getmypid();
    @mkdir($tempStorage . '/logs', 0755, true);
    $this->app->useStoragePath($tempStorage);
});

afterEach(function () {
    $tempStorage = sys_get_temp_dir() . '/kneadit_test_storage_' . getmypid();
    @rmdir($tempStorage . '/logs');
    @rmdir($tempStorage);
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
