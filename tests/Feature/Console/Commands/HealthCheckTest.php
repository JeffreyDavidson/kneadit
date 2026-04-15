<?php

use App\Events\Platform\HealthCheckFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
    config(['mail.platform_notify' => 'test@example.com']);

    // Point storage to a real writable temp directory so the health check passes
    $tempStorage = sys_get_temp_dir() . '/kneadit_test_storage_' . getmypid();
    @mkdir($tempStorage . '/logs', 0755, true);
    $this->app->useStoragePath($tempStorage);

    // Ensure tenant DB directory exists and is writable for health check
    $tenantDbDir = database_path();
    config(['tenancy.tenant_db_path' => $tenantDbDir]);
});

afterEach(function () {
    $tempStorage = sys_get_temp_dir() . '/kneadit_test_storage_' . getmypid();
    @rmdir($tempStorage . '/logs');
    @rmdir($tempStorage);
});

test('health check command exists', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('OK', 200)]);

    $this->artisan('health:check')
        ->assertSuccessful();
});

test('health check verifies database connection', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('OK', 200)]);

    $this->artisan('health:check')
        ->expectsOutputToContain('Database connection OK')
        ->assertSuccessful();
});

test('health check verifies users table', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('OK', 200)]);

    $this->artisan('health:check')
        ->expectsOutputToContain('Users table OK')
        ->assertSuccessful();
});

test('health check verifies disk space', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('OK', 200)]);

    $this->artisan('health:check')
        ->expectsOutputToContain('Disk space OK')
        ->assertSuccessful();
});

test('health check verifies storage writable', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('OK', 200)]);

    $this->artisan('health:check')
        ->expectsOutputToContain('Storage/logs writable')
        ->assertSuccessful();
});

test('health check detects homepage failure', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('Server Error', 500)]);

    $this->artisan('health:check')
        ->assertFailed();
});

test('health check dispatches event on failure', function () {
    Event::fake([HealthCheckFailed::class]);

    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('Server Error', 500)]);

    $this->artisan('health:check')
        ->expectsOutputToContain('alert dispatched')
        ->assertFailed();

    Event::assertDispatched(HealthCheckFailed::class, function ($event) {
        return str_contains($event->message, 'Health Check Alert');
    });
});

test('health check detects homepage connection failure', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => fn () => throw new Illuminate\Http\Client\ConnectionException('Connection refused')]);

    $this->artisan('health:check')
        ->expectsOutputToContain('Homepage unreachable')
        ->assertFailed();
});

test('health check reports all passing checks', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('OK', 200)]);

    $this->artisan('health:check')
        ->expectsOutputToContain('All health checks passed')
        ->assertSuccessful();
});

test('health check detects non-writable storage logs', function () {
    Event::fake([HealthCheckFailed::class]);
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('OK', 200)]);

    // Point storage to a non-existent directory
    $nonExistentDir = sys_get_temp_dir() . '/kneadit_nonexistent_' . getmypid() . '_' . mt_rand();
    $this->app->useStoragePath($nonExistentDir);

    $this->artisan('health:check')
        ->expectsOutputToContain('Storage/logs')
        ->assertFailed();
});

test('health check verifies tenant db directory', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('OK', 200)]);

    $this->artisan('health:check')
        ->expectsOutputToContain('Tenant DB directory')
        ->assertSuccessful();
});
