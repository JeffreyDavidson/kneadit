<?php

use App\Enums\Marketing\CustomerCampaignStatus;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Events\Customers\CustomerBirthday;
use App\Events\Customers\RepeatOrderReminderDue;
use App\Mail\Customers\AbandonedCartRecoveryMail;
use App\Mail\Customers\CustomerCampaignMail;
use App\Mail\Customers\ReviewRequestMail;
use App\Mail\Operations\LowStockAlertMail;
use App\Models\Customers\Customer;
use App\Models\Engagement\CustomerCampaign;
use App\Models\Financial\Coupon;
use App\Models\Inventory\Ingredient;
use App\Models\Orders\Cart;
use App\Models\Orders\CartItem;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use JMac\Testing\Double;
use Stancl\Tenancy\Tenancy;

use function Pest\Laravel\artisan;

beforeEach(fn () => setUpCentralTest());

/**
 * Runs the real command and TenancyManager against one bakery row; the Tenancy
 * double only swaps the database switch, because the test database holds every
 * tenant's tables.
 *
 * @param  array<string, mixed>  $options
 */
function runCommandForBakery(string $command, ?string $pausedAt, array $options = []): void
{
    createTenant(['id' => 'paused-bakery', 'paused_at' => $pausedAt]);

    $tenancy = Double::for(Tenancy::class);
    $tenancy->allows('initialize');
    $tenancy->allows('end');
    app()->instance(Tenancy::class, $tenancy);

    artisan($command, $options)->assertSuccessful();
}

dataset('bakery states', [
    'running bakery' => [null, 1],
    'paused bakery' => ['2026-10-04 12:00:00', 0],
]);

test('birthday emails skip a paused bakery', function (?string $pausedAt, int $sent) {
    Event::fake([CustomerBirthday::class]);
    Date::setTestNow('2026-10-05 12:00');
    settings(['birthday_program_enabled' => '1', 'birthday_coupon_enabled' => '0']);
    Customer::factory()->create(['email' => 'birthday@example.com', 'birthday' => '1990-10-05']);

    runCommandForBakery('birthday:send-emails', $pausedAt, ['--force' => true]);

    Event::assertDispatchedTimes(CustomerBirthday::class, $sent);
})->with('bakery states');

test('repeat order reminders skip a paused bakery', function (?string $pausedAt, int $sent) {
    Event::fake([RepeatOrderReminderDue::class]);
    Date::setTestNow('2026-10-05 12:00');
    settings(['repeat_reminders_enabled' => '1', 'repeat_reminder_days' => '14']);
    Order::factory()
        ->for(Customer::factory()->create(['email' => 'loyal@example.com']))
        ->create(['payment_status' => PaymentStatus::Paid, 'delivery_date' => '2026-08-01']);

    runCommandForBakery('orders:send-repeat-reminders', $pausedAt, ['--force' => true]);

    Event::assertDispatchedTimes(RepeatOrderReminderDue::class, $sent);
})->with('bakery states');

test('review requests skip a paused bakery', function (?string $pausedAt, int $sent) {
    Mail::fake();
    Date::setTestNow('2026-10-05 12:00');
    settings(['review_requests_enabled' => '1', 'review_request_delay_hours' => '24']);
    Order::factory()
        ->for(Customer::factory()->state(['email' => 'buyer@example.com']))
        ->create([
            'status' => OrderStatus::Delivered,
            'review_request_sent_at' => null,
            'updated_at' => now()->subHours(48),
        ]);

    runCommandForBakery('reviews:send-requests', $pausedAt);

    Mail::assertQueuedCount($sent);
    Mail::assertQueued(ReviewRequestMail::class, $sent);
})->with('bakery states');

test('cart recovery skips a paused bakery and mints no coupon', function (?string $pausedAt, int $sent) {
    Mail::fake();
    Date::setTestNow('2026-10-05 12:00');
    settings([
        'abandoned_cart_recovery_enabled' => '1',
        'abandoned_cart_recovery_hours' => '24',
        'abandoned_cart_recovery_coupon_dollars' => '5',
    ]);
    Customer::factory()->create(['email' => 'buyer@example.com']);
    $cart = Cart::factory()->withEmail('buyer@example.com')->create(['last_activity_at' => '2026-10-03 12:00']);
    CartItem::factory()->for($cart)->create();

    runCommandForBakery('carts:send-abandonment-emails', $pausedAt);

    Mail::assertQueued(AbandonedCartRecoveryMail::class, $sent);
    expect(Coupon::query()->count())->toBe($sent);
})->with('bakery states');

test('scheduled customer campaigns skip a paused bakery', function (?string $pausedAt, int $sent) {
    Mail::fake();
    Date::setTestNow('2026-10-05 12:00');
    $customer = Customer::factory()->create();
    Order::factory()->for($customer)->paid()->create(['delivery_date' => '2026-09-30']);
    $campaign = CustomerCampaign::factory()->create([
        'status' => CustomerCampaignStatus::Scheduled,
        'scheduled_at' => now()->subMinute(),
    ]);

    runCommandForBakery('campaigns:send-scheduled', $pausedAt);

    Mail::assertQueued(CustomerCampaignMail::class, $sent);
    expect($campaign->fresh()->status)->toBe($sent === 1 ? CustomerCampaignStatus::Sent : CustomerCampaignStatus::Scheduled);
})->with('bakery states');

test('the low-stock alert still reaches the owner of a paused bakery', function () {
    Mail::fake();
    Date::setTestNow('2026-10-05 12:00');
    settings(['low_stock_alerts_enabled' => '1', 'store_email' => 'baker@example.com']);
    Ingredient::factory()->lowStock()->create();

    runCommandForBakery('inventory:send-low-stock-alert', '2026-10-04 12:00:00', ['--force' => true]);

    Mail::assertQueued(LowStockAlertMail::class, fn (LowStockAlertMail $mail): bool => $mail->hasTo('baker@example.com'));
});
