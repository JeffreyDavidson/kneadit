<?php

use App\Models\Operations\ScheduledNotificationRun;
use App\Services\Scheduling\LocalSendSchedule;
use App\Services\Scheduling\LocalSendWindow;
use App\Services\Settings\TenantSettings;
use Carbon\WeekDay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\assertDatabaseHas;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->window = resolve(LocalSendWindow::class);
    test()->daily = new LocalSendSchedule('test:daily', 8);
});

function settingsWithTimezone(string $timezone): TenantSettings
{
    return makeTenantSettings(orders: makeOrderSettings(['timezone' => $timezone]));
}

test('runs the send only at the bakery-local hour', function (string $timezone, string $nowUtc, bool $runs) {
    Date::setTestNow($nowUtc);
    $sent = 0;

    test()->window->run(test()->daily, settingsWithTimezone($timezone), false, function () use (&$sent): void {
        $sent++;
    });

    expect($sent)->toBe($runs ? 1 : 0);
})->with([
    'New York 08:00 EDT' => ['America/New_York', '2026-10-05 12:00', true],
    'New York 08:59 EDT' => ['America/New_York', '2026-10-05 12:59', true],
    'New York 09:00 EDT' => ['America/New_York', '2026-10-05 13:00', false],
    'New York 08:00 EST after the autumn clock change' => ['America/New_York', '2026-11-02 13:00', true],
    'New York 07:00 EST after the autumn clock change' => ['America/New_York', '2026-11-02 12:00', false],
    'Hawaii 08:00' => ['Pacific/Honolulu', '2026-10-05 18:00', true],
    'Kolkata 08:30 local at the top of the UTC hour' => ['Asia/Kolkata', '2026-10-05 03:00', true],
    'UTC 08:00' => ['UTC', '2026-10-05 08:00', true],
    'UTC 12:00' => ['UTC', '2026-10-05 12:00', false],
]);

test('runs a weekday schedule only on that bakery-local weekday', function (string $timezone, string $nowUtc, bool $runs) {
    Date::setTestNow($nowUtc);
    $sent = 0;
    $weekly = new LocalSendSchedule('test:weekly', 8, WeekDay::Monday);

    test()->window->run($weekly, settingsWithTimezone($timezone), false, function () use (&$sent): void {
        $sent++;
    });

    expect($sent)->toBe($runs ? 1 : 0);
})->with([
    'New York Monday 08:00' => ['America/New_York', '2026-10-05 12:00', true],
    'New York Tuesday 08:00' => ['America/New_York', '2026-10-06 12:00', false],
    'Sydney Monday 08:00 while it is Sunday in UTC' => ['Australia/Sydney', '2026-10-04 21:00', true],
    'Honolulu Sunday 08:00 while it is Sunday in UTC' => ['Pacific/Honolulu', '2026-10-04 18:00', false],
]);

test('runs at most once per bakery-local date', function () {
    Date::setTestNow('2026-10-05 12:00');
    $sent = 0;
    $send = function () use (&$sent): void {
        $sent++;
    };
    $settings = settingsWithTimezone('America/New_York');

    test()->window->run(test()->daily, $settings, false, $send);
    Date::setTestNow('2026-10-05 12:45');
    test()->window->run(test()->daily, $settings, false, $send);

    expect($sent)->toBe(1);
    assertDatabaseHas(ScheduledNotificationRun::class, ['notification_key' => 'local-send:test:daily:2026-10-05']);
});

test('runs again on the next bakery-local date', function () {
    $sent = 0;
    $send = function () use (&$sent): void {
        $sent++;
    };
    $settings = settingsWithTimezone('America/New_York');

    Date::setTestNow('2026-10-05 12:00');
    test()->window->run(test()->daily, $settings, false, $send);
    Date::setTestNow('2026-10-06 12:00');
    test()->window->run(test()->daily, $settings, false, $send);

    expect($sent)->toBe(2);
});

test('keys the marker by the bakery-local date rather than the UTC date', function () {
    Date::setTestNow('2026-10-04 21:00');

    test()->window->run(test()->daily, settingsWithTimezone('Australia/Sydney'), false, fn () => null);

    assertDatabaseHas(ScheduledNotificationRun::class, ['notification_key' => 'local-send:test:daily:2026-10-05']);
});

test('releases the marker when the send fails so it can be retried', function () {
    Date::setTestNow('2026-10-05 12:00');
    $settings = settingsWithTimezone('America/New_York');
    $attempts = 0;

    $fail = function () use (&$attempts): void {
        $attempts++;

        throw new RuntimeException('mail server down');
    };

    expect(fn () => test()->window->run(test()->daily, $settings, false, $fail))->toThrow(RuntimeException::class, 'mail server down');

    test()->window->run(test()->daily, $settings, false, function () use (&$attempts): void {
        $attempts++;
    });

    expect($attempts)->toBe(2);
});

test('force runs outside the send hour without recording a marker', function () {
    Date::setTestNow('2026-10-05 18:00');
    $sent = 0;

    test()->window->run(test()->daily, settingsWithTimezone('America/New_York'), true, function () use (&$sent): void {
        $sent++;
    });

    expect($sent)->toBe(1)
        ->and(ScheduledNotificationRun::query()->where('notification_key', 'like', 'local-send:%')->count())->toBe(0);
});
