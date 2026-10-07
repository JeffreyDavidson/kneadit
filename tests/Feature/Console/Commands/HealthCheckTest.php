<?php

use App\Mail\Platform\HealthAlertMail;
use App\Services\Platform\HealthChecks\HomepageRespondsCheck;
use App\Services\Platform\ScheduledTaskMonitor;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

use function Pest\Laravel\artisan;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
    config(['mail.platform_notify' => 'test@example.com']);

    // A scheduler that has run recently, so the heartbeat check passes.
    resolve(ScheduledTaskMonitor::class)->started('health:check');

    // A backup that has just run, so the backup check passes.
    test()->backupPath = sys_get_temp_dir().'/kneadit_test_backups_'.getmypid();
    File::ensureDirectoryExists(test()->backupPath.'/'.now()->format('Y-m-d_H-i-s'));
    config(['backups.path' => test()->backupPath]);

    // Point storage to a real writable temp directory so the health check passes
    $tempStorage = sys_get_temp_dir().'/kneadit_test_storage_'.getmypid();
    @mkdir($tempStorage.'/logs', 0755, true);
    $this->app->useStoragePath($tempStorage);

    // Ensure tenant DB directory exists and is writable for health check
    $tenantDbDir = database_path();
    config(['tenancy.tenant_db_path' => $tenantDbDir]);
});

afterEach(function () {
    File::deleteDirectory(test()->backupPath);

    $tempStorage = sys_get_temp_dir().'/kneadit_test_storage_'.getmypid();
    @rmdir($tempStorage.'/logs');
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

test('health check verifies the scheduler heartbeat', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('OK', 200)]);

    $this->artisan('health:check')
        ->expectsOutputToContain('Scheduler running')
        ->assertSuccessful();
});

test('health check fails and alerts when the scheduler has gone quiet', function () {
    Mail::fake();
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('OK', 200)]);
    Date::setTestNow(now()->addMinutes(71));

    $this->artisan('health:check')
        ->expectsOutputToContain('Scheduler silent')
        ->assertFailed();

    Mail::assertSent(HealthAlertMail::class, fn (HealthAlertMail $mail): bool => str_contains($mail->alertMessage, 'Scheduler silent'));
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

describe('alerting', function () {
    beforeEach(function () {
        Mail::fake();
        Queue::fake();
        Http::preventStrayRequests();
    });

    test('a failing check sends one alert mail straight away, without the queue', function () {
        Http::fake(['*' => Http::response('Server Error', 500)]);

        artisan('health:check')->assertFailed();

        Mail::assertSent(HealthAlertMail::class, fn (HealthAlertMail $mail): bool => $mail->hasTo('test@example.com')
            && str_contains($mail->alertMessage, 'Health Check Alert'));
        Mail::assertSentCount(1);
        Mail::assertNothingQueued();
        Queue::assertNothingPushed();
    });

    test('the same failing check does not alert again inside the cooldown', function () {
        Http::fake(['*' => Http::response('Server Error', 500)]);
        Date::setTestNow('2026-10-05 12:00');
        resolve(ScheduledTaskMonitor::class)->started('health:check');

        artisan('health:check')->assertFailed();
        Date::setTestNow('2026-10-05 17:59');
        resolve(ScheduledTaskMonitor::class)->started('health:check');
        artisan('health:check')->assertFailed();

        Mail::assertSentCount(1);
    });

    test('the same failing check alerts again once the cooldown has passed', function () {
        Http::fake(['*' => Http::response('Server Error', 500)]);
        Date::setTestNow('2026-10-05 12:00');
        resolve(ScheduledTaskMonitor::class)->started('health:check');

        artisan('health:check')->assertFailed();
        Date::setTestNow('2026-10-05 18:01');
        resolve(ScheduledTaskMonitor::class)->started('health:check');
        artisan('health:check')->assertFailed();

        Mail::assertSentCount(2);
    });

    test('a check inside its cooldown does not hold back another failing check', function () {
        Http::fake(['*' => Http::response('Server Error', 500)]);
        Cache::put('health-check-alert:'.HomepageRespondsCheck::class, true, now()->addHours(6));
        app()->useStoragePath(sys_get_temp_dir().'/kneadit_nonexistent_'.getmypid().'_'.Str::random());

        artisan('health:check')->assertFailed();

        Mail::assertSentCount(1);
        Mail::assertSent(HealthAlertMail::class, fn (HealthAlertMail $mail): bool => str_contains($mail->alertMessage, 'Storage/logs')
            && ! str_contains($mail->alertMessage, 'Homepage'));
    });

    test('a check that passes again sends one recovered mail', function () {
        $homepageUp = false;
        Http::fake(['*' => function () use (&$homepageUp) {
            return Http::response('body', $homepageUp ? 200 : 500);
        }]);

        artisan('health:check')->assertFailed();
        $homepageUp = true;
        artisan('health:check')->assertSuccessful();
        artisan('health:check')->assertSuccessful();

        Mail::assertSentCount(2);
        Mail::assertSent(HealthAlertMail::class, fn (HealthAlertMail $mail): bool => str_contains($mail->alertMessage, 'Recovered'));
    });

    test('a mail failure is logged and does not hide the failing check', function () {
        Http::fake(['*' => Http::response('Server Error', 500)]);
        Mail::shouldReceive('sendNow')->andThrow(new RuntimeException('SMTP down'));
        Log::shouldReceive('critical')->once();
        Log::shouldReceive('error')->once()->withArgs(fn (string $message): bool => str_contains($message, 'Health alert'));

        artisan('health:check')
            ->expectsOutputToContain('Homepage')
            ->assertFailed();

    });

    test('a failing cache does not stop the alert', function () {
        Http::fake(['*' => Http::response('Server Error', 500)]);
        Cache::shouldReceive('has')->andThrow(new RuntimeException('cache down'));
        Cache::shouldReceive('put')->andThrow(new RuntimeException('cache down'));
        Cache::shouldReceive('pull')->andThrow(new RuntimeException('cache down'));

        artisan('health:check')->assertFailed();

        Mail::assertSentCount(1);
    });
});

test('health check detects homepage connection failure', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => fn () => throw new ConnectionException('Connection refused')]);

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
    Mail::fake();
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('OK', 200)]);

    // Point storage to a non-existent directory
    $nonExistentDir = sys_get_temp_dir().'/kneadit_nonexistent_'.getmypid().'_'.Str::random();
    $this->app->useStoragePath($nonExistentDir);

    $this->artisan('health:check')
        ->expectsOutputToContain('Storage/logs')
        ->assertFailed();
});

test('health check verifies a recent database backup', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('OK', 200)]);

    $this->artisan('health:check')
        ->expectsOutputToContain('Database backup OK')
        ->assertSuccessful();
});

test('health check fails and alerts when the newest database backup is stale', function () {
    Mail::fake();
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('OK', 200)]);
    File::deleteDirectory(test()->backupPath);
    File::ensureDirectoryExists(test()->backupPath.'/'.now()->subHours(14)->format('Y-m-d_H-i-s'));

    $this->artisan('health:check')
        ->expectsOutputToContain('Database backup stale')
        ->assertFailed();

    Mail::assertSent(HealthAlertMail::class, fn (HealthAlertMail $mail): bool => str_contains($mail->alertMessage, 'Database backup stale'));
});

test('health check fails and alerts when the last database backup run failed', function () {
    Mail::fake();
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('OK', 200)]);
    resolve(ScheduledTaskMonitor::class)->started('backup:databases');
    resolve(ScheduledTaskMonitor::class)->succeeded('backup:databases', 3.0, 1);

    $this->artisan('health:check')
        ->expectsOutputToContain('Database backup failed')
        ->assertFailed();

    Mail::assertSent(HealthAlertMail::class, fn (HealthAlertMail $mail): bool => str_contains($mail->alertMessage, 'Database backup failed'));
});

test('health check verifies tenant db directory', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('OK', 200)]);

    $this->artisan('health:check')
        ->expectsOutputToContain('Tenant DB directory')
        ->assertSuccessful();
});
