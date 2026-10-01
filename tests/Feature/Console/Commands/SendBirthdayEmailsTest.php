<?php

use App\Events\Customers\CustomerBirthday;
use App\Models\Customers\Customer;
use App\Models\Operations\ScheduledNotificationRun;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    setUpCentralTest();
    runCommandsAsOneTenant();
});

test('birthday emails command runs successfully with no tenants', function () {
    Mail::fake();

    $this->artisan('birthday:send-emails')
        ->assertSuccessful();

    Mail::assertNothingSent();
});

describe('bakery-local send hour', function () {
    beforeEach(function () {
        Event::fake([CustomerBirthday::class]);
        settings(['birthday_program_enabled' => '1']);
        Customer::factory()->create(['email' => 'birthday@example.com', 'birthday' => '1990-10-05']);
    });

    test('sends only when the bakery-local clock reads 08:00', function (string $timezone, string $now, int $sent) {
        settings(['timezone' => $timezone]);
        Date::setTestNow($now);

        artisan('birthday:send-emails')->assertSuccessful();

        Event::assertDispatchedTimes(CustomerBirthday::class, $sent);
    })->with([
        'New York at 08:00 local' => ['America/New_York', '2026-10-05 12:00', 1],
        'New York at 07:00 local' => ['America/New_York', '2026-10-05 11:00', 0],
        'New York at 09:00 local (13:00 UTC)' => ['America/New_York', '2026-10-05 13:00', 0],
        'New York at 08:00 UTC, which is 04:00 local' => ['America/New_York', '2026-10-05 08:00', 0],
        'Hawaii at 08:00 local' => ['Pacific/Honolulu', '2026-10-05 18:00', 1],
        'UTC at 08:00' => ['UTC', '2026-10-05 08:00', 1],
        'UTC at 09:00' => ['UTC', '2026-10-05 09:00', 0],
    ]);

    test('records a marker for the bakery-local date', function () {
        settings(['timezone' => 'America/New_York']);
        Date::setTestNow('2026-10-05 12:00');

        artisan('birthday:send-emails')->assertSuccessful();

        assertDatabaseHas(ScheduledNotificationRun::class, [
            'notification_key' => 'local-send:birthday:send-emails:2026-10-05',
        ]);
    });

    test('sends once when run twice in the same local hour', function () {
        settings(['timezone' => 'America/New_York']);
        Date::setTestNow('2026-10-05 12:00');

        artisan('birthday:send-emails')->assertSuccessful();
        Date::setTestNow('2026-10-05 12:30');
        artisan('birthday:send-emails')->assertSuccessful();

        Event::assertDispatchedTimes(CustomerBirthday::class, 1);
    });

    test('the local-day marker blocks a rerun even without the per-customer claims', function () {
        settings(['timezone' => 'America/New_York']);
        Date::setTestNow('2026-10-05 12:00');

        artisan('birthday:send-emails')->assertSuccessful();
        ScheduledNotificationRun::query()->where('notification_key', 'like', 'engagement:%')->delete();
        artisan('birthday:send-emails')->assertSuccessful();

        Event::assertDispatchedTimes(CustomerBirthday::class, 1);
    });

    test('--force sends outside the local send hour', function () {
        settings(['timezone' => 'America/New_York']);
        Date::setTestNow('2026-10-05 15:00');

        artisan('birthday:send-emails', ['--force' => true])->assertSuccessful();

        Event::assertDispatchedTimes(CustomerBirthday::class, 1);
    });

    test('--force still sends each customer once per day', function () {
        settings(['timezone' => 'America/New_York']);
        Date::setTestNow('2026-10-05 15:00');

        artisan('birthday:send-emails', ['--force' => true])->assertSuccessful();
        artisan('birthday:send-emails', ['--force' => true])->assertSuccessful();

        Event::assertDispatchedTimes(CustomerBirthday::class, 1);
    });
});
