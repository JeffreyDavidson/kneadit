<?php

use App\Mail\Operations\LowStockAlertMail;
use App\Models\Inventory\Ingredient;
use App\Models\Operations\ScheduledNotificationRun;
use App\Services\Notifications\ScheduledNotificationRunTracker;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => setUpCentralTest());

test('inventory:send-low-stock-alert command runs successfully with no tenants', function () {
    Mail::fake();

    $this->artisan('inventory:send-low-stock-alert')->assertSuccessful();

    Mail::assertNothingQueued();
});

test('command source uses TenancyManager for tenant context', function () {
    $source = file_get_contents(app_path('Console/Commands/Operations/SendLowStockAlertCommand.php'));

    expect($source)
        ->toContain('TenancyManager')
        ->toContain('forEachTenant')
        ->toContain('lowStockAlertsEnabled');
});

test('command source uses a per-tenant daily idempotency key', function () {
    $source = file_get_contents(app_path('Console/Commands/Operations/SendLowStockAlertCommand.php'));

    expect($source)
        ->toContain(ScheduledNotificationRunTracker::class)
        ->toContain('low-stock:')
        ->toContain('toDateString()');
});

describe('bakery-local send hour', function () {
    beforeEach(function () {
        runCommandsAsOneTenant();
        Mail::fake();
        settings(['low_stock_alerts_enabled' => '1', 'store_email' => 'baker@example.com']);
        Ingredient::factory()->lowStock()->create();
    });

    test('queues the alert only when the bakery-local clock reads 07:00', function (string $timezone, string $now, int $queued) {
        settings(['timezone' => $timezone]);
        Date::setTestNow($now);

        artisan('inventory:send-low-stock-alert')->assertSuccessful();

        Mail::assertQueuedCount($queued);
    })->with([
        'New York at 07:00 local' => ['America/New_York', '2026-10-05 11:00', 1],
        'New York at 08:00 local' => ['America/New_York', '2026-10-05 12:00', 0],
        'New York at 07:00 UTC, which is 03:00 local' => ['America/New_York', '2026-10-05 07:00', 0],
        'Hawaii at 07:00 local' => ['Pacific/Honolulu', '2026-10-05 17:00', 1],
        'UTC at 07:00' => ['UTC', '2026-10-05 07:00', 1],
        'UTC at 11:00' => ['UTC', '2026-10-05 11:00', 0],
    ]);

    test('records a marker for the bakery-local date', function () {
        settings(['timezone' => 'America/New_York']);
        Date::setTestNow('2026-10-05 11:00');

        artisan('inventory:send-low-stock-alert')->assertSuccessful();

        assertDatabaseHas(ScheduledNotificationRun::class, [
            'notification_key' => 'local-send:inventory:send-low-stock-alert:2026-10-05',
        ]);
    });

    test('queues once when run twice in the same local hour', function () {
        settings(['timezone' => 'America/New_York']);
        Date::setTestNow('2026-10-05 11:00');

        artisan('inventory:send-low-stock-alert')->assertSuccessful();
        Date::setTestNow('2026-10-05 11:30');
        artisan('inventory:send-low-stock-alert')->assertSuccessful();

        Mail::assertQueuedCount(1);
        Mail::assertQueued(LowStockAlertMail::class);
    });

    test('--force queues the alert outside the local send hour', function () {
        settings(['timezone' => 'America/New_York']);
        Date::setTestNow('2026-10-05 15:00');

        artisan('inventory:send-low-stock-alert', ['--force' => true])->assertSuccessful();

        Mail::assertQueuedCount(1);
    });
});
