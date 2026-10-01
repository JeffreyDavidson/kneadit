<?php

use App\Events\Platform\WeeklyDigestRequested;
use App\Models\Operations\ScheduledNotificationRun;
use App\Models\Staff\User;
use App\Services\Notifications\ScheduledNotificationRunTracker;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use JMac\Testing\Double;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => setUpCentralTest());

test('digest:weekly command runs successfully with no tenants', function () {
    $this->artisan('digest:weekly')
        ->assertSuccessful();
});

test('digest:weekly skips tenants with digest disabled', function () {
    Event::fake([WeeklyDigestRequested::class]);

    createTenant([
        'id' => 'digest-disabled',
        'name' => 'Disabled Baker',
        'email' => 'disabled@test.com',
    ]);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(
            // Simulate settings returning '0' for digest
            fn ($tenant, $callback) => null);

    app()->instance(TenancyManager::class, $tenancyManager);

    $this->artisan('digest:weekly')
        ->assertSuccessful();

    Event::assertNotDispatched(WeeklyDigestRequested::class);
});

test('digest:weekly returns failure when tenant processing fails', function () {
    createTenant([
        'id' => 'failing-bakery',
        'name' => 'Failing Baker',
        'email' => 'failing@test.com',
    ]);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->throws(new RuntimeException('Tenant DB unavailable'));

    app()->instance(TenancyManager::class, $tenancyManager);

    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn ($message) => str_contains($message, 'Weekly digest processing failed'));

    $this->artisan('digest:weekly')
        ->expectsOutputToContain('Failed')
        ->assertFailed();
});

test('digest:weekly command source resolves WeeklyDigestDataCollector', function () {
    $source = file_get_contents(app_path('Console/Commands/Platform/SendWeeklyDigestCommand.php'));

    expect($source)
        ->toContain('WeeklyDigestDataCollector')
        ->toContain('WeeklyDigestRequested')
        ->toContain('withinTenant');
});

test('digest:weekly command source checks for owner users first', function () {
    $source = file_get_contents(app_path('Console/Commands/Platform/SendWeeklyDigestCommand.php'));

    expect($source)
        ->toContain('owners()')
        ->toContain('limit(1)');
});

test('digest:weekly command uses a per-user weekly idempotency key', function () {
    $source = file_get_contents(app_path('Console/Commands/Platform/SendWeeklyDigestCommand.php'));

    expect($source)
        ->toContain(ScheduledNotificationRunTracker::class)
        ->toContain('weekly-digest:')
        ->toContain('startOfWeek()');
});

describe('bakery-local send time', function () {
    beforeEach(function () {
        runCommandsAsOneTenant();
        Event::fake([WeeklyDigestRequested::class]);
        createTenant(['id' => 'test-bakery']);
        User::factory()->owner()->create();
    });

    test('sends only on Monday when the bakery-local clock reads 08:00', function (string $timezone, string $now, int $sent) {
        settings(['timezone' => $timezone]);
        Date::setTestNow($now);

        artisan('digest:weekly')->assertSuccessful();

        Event::assertDispatchedTimes(WeeklyDigestRequested::class, $sent);
    })->with([
        'New York Monday at 08:00 local' => ['America/New_York', '2026-10-05 12:00', 1],
        'New York Monday at 09:00 local' => ['America/New_York', '2026-10-05 13:00', 0],
        'New York Tuesday at 08:00 local' => ['America/New_York', '2026-10-06 12:00', 0],
        'New York Sunday at 08:00 local' => ['America/New_York', '2026-10-04 12:00', 0],
        'New York Monday 08:00 UTC, which is 04:00 local' => ['America/New_York', '2026-10-05 08:00', 0],
        'Sydney local Monday 08:00, still Sunday in UTC' => ['Australia/Sydney', '2026-10-04 21:00', 1],
        'Sydney Monday 08:00 UTC, which is Monday 19:00 local' => ['Australia/Sydney', '2026-10-05 08:00', 0],
        'UTC Monday at 08:00' => ['UTC', '2026-10-05 08:00', 1],
        'UTC Monday at 12:00' => ['UTC', '2026-10-05 12:00', 0],
    ]);

    test('records a marker for the bakery-local date', function () {
        settings(['timezone' => 'America/New_York']);
        Date::setTestNow('2026-10-05 12:00');

        artisan('digest:weekly')->assertSuccessful();

        assertDatabaseHas(ScheduledNotificationRun::class, [
            'notification_key' => 'local-send:digest:weekly:2026-10-05',
        ]);
    });

    test('the local-day marker blocks a rerun even without the per-user claim', function () {
        settings(['timezone' => 'America/New_York']);
        Date::setTestNow('2026-10-05 12:00');

        artisan('digest:weekly')->assertSuccessful();
        ScheduledNotificationRun::query()->where('notification_key', 'like', 'weekly-digest:%')->delete();
        artisan('digest:weekly')->assertSuccessful();

        Event::assertDispatchedTimes(WeeklyDigestRequested::class, 1);
    });

    test('--force sends outside the local send time', function () {
        settings(['timezone' => 'America/New_York']);
        Date::setTestNow('2026-10-07 18:00');

        artisan('digest:weekly', ['--force' => true])->assertSuccessful();

        Event::assertDispatchedTimes(WeeklyDigestRequested::class, 1);
    });
});
