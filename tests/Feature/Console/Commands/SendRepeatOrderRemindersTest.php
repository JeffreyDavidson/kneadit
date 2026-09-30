<?php

use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Operations\ScheduledNotificationRun;
use App\Models\Orders\Order;
use App\Services\Engagement\Engagements\RepeatOrderReminderEngagement;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    setUpCentralTest();
    runCommandsAsOneTenant();
});

/**
 * The dispatcher claims one key per customer it processes, so the claims show
 * whether the command reached the customers regardless of what the reminder
 * event itself goes on to do.
 */
function processedReminderCustomers(): int
{
    return ScheduledNotificationRun::query()
        ->where('notification_key', 'like', 'engagement:'.RepeatOrderReminderEngagement::class.':%')
        ->count();
}

test('orders:send-repeat-reminders command runs successfully with no tenants', function () {
    Mail::fake();

    $this->artisan('orders:send-repeat-reminders')
        ->assertSuccessful();

    Mail::assertNothingSent();
});

describe('bakery-local send hour', function () {
    beforeEach(function () {
        settings(['repeat_reminders_enabled' => '1', 'repeat_reminder_days' => '14']);
        Order::factory()
            ->for(Customer::factory()->create(['email' => 'loyal@example.com']))
            ->create([
                'payment_status' => PaymentStatus::Paid,
                'delivery_date' => '2026-08-01',
            ]);
    });

    test('processes customers only when the bakery-local clock reads 10:00', function (string $timezone, string $now, int $processed) {
        settings(['timezone' => $timezone]);
        Date::setTestNow($now);

        artisan('orders:send-repeat-reminders')->assertSuccessful();

        expect(processedReminderCustomers())->toBe($processed);
    })->with([
        'New York at 10:00 local' => ['America/New_York', '2026-10-05 14:00', 1],
        'New York at 09:00 local' => ['America/New_York', '2026-10-05 13:00', 0],
        'New York at 11:00 local' => ['America/New_York', '2026-10-05 15:00', 0],
        'New York at 10:00 UTC, which is 06:00 local' => ['America/New_York', '2026-10-05 10:00', 0],
        'UTC at 10:00' => ['UTC', '2026-10-05 10:00', 1],
        'UTC at 14:00' => ['UTC', '2026-10-05 14:00', 0],
    ]);

    test('records a marker for the bakery-local date', function () {
        settings(['timezone' => 'America/New_York']);
        Date::setTestNow('2026-10-05 14:00');

        artisan('orders:send-repeat-reminders')->assertSuccessful();

        assertDatabaseHas(ScheduledNotificationRun::class, [
            'notification_key' => 'local-send:orders:send-repeat-reminders:2026-10-05',
        ]);
    });

    test('the local-day marker blocks a rerun in the same local hour even without the per-customer claims', function () {
        settings(['timezone' => 'America/New_York']);
        Date::setTestNow('2026-10-05 14:00');

        artisan('orders:send-repeat-reminders')->assertSuccessful();
        ScheduledNotificationRun::query()->where('notification_key', 'like', 'engagement:%')->delete();
        Date::setTestNow('2026-10-05 14:30');
        artisan('orders:send-repeat-reminders')->assertSuccessful();

        expect(processedReminderCustomers())->toBe(0);
    });

    test('--force processes customers outside the local send hour', function () {
        settings(['timezone' => 'America/New_York']);
        Date::setTestNow('2026-10-05 18:00');

        artisan('orders:send-repeat-reminders', ['--force' => true])->assertSuccessful();

        expect(processedReminderCustomers())->toBe(1);
    });
});
