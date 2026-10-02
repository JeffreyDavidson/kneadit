<?php

declare(strict_types=1);

use App\Actions\Marketing\SendBulkCustomerMessage;
use App\Actions\Marketing\SendCustomerCampaign;
use App\Enums\Marketing\BulkMessagePurpose;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Events\Customers\CustomerBirthday;
use App\Events\Customers\RepeatOrderReminderDue;
use App\Events\Customers\ReviewRequested;
use App\Listeners\Customers\SendHappyBirthdayEmailListener;
use App\Listeners\Customers\SendRepeatOrderReminderEmailListener;
use App\Listeners\Customers\SendReviewRequestEmailListener;
use App\Mail\Customers\AbandonedCartRecoveryMail;
use App\Mail\Customers\BulkCustomerMessageMail;
use App\Mail\Customers\CustomerCampaignMail;
use App\Models\Customers\Customer;
use App\Models\Engagement\CustomerCampaign;
use App\Models\Engagement\CustomerCampaignLog;
use App\Models\Orders\Cart;
use App\Models\Orders\CartItem;
use App\Models\Orders\Order;
use App\Services\Engagement\Engagements\BirthdayEngagement;
use App\Services\Engagement\Engagements\RepeatOrderReminderEngagement;
use App\Services\Engagement\Engagements\ReviewRequestEngagement;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\artisan;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

/**
 * Each case arranges whatever the sender needs for the given customer, runs the
 * sender, and reports whether the customer would receive a marketing mail.
 */
dataset('marketing senders', [
    'customer campaign' => [function (Customer $customer): bool {
        Order::factory()->for($customer)->paid()->create(['delivery_date' => now()->subDays(5)->toDateString()]);
        $campaign = CustomerCampaign::factory()->create(['target_segment' => 'all']);

        resolve(SendCustomerCampaign::class)($campaign);

        return Mail::queued(CustomerCampaignMail::class)->isNotEmpty();
    }],
    'bulk customer message' => [function (Customer $customer): bool {
        resolve(SendBulkCustomerMessage::class)([$customer], BulkMessagePurpose::Promotion, 'Subject', 'Body');

        return Mail::queued(BulkCustomerMessageMail::class)->isNotEmpty();
    }],
    'birthday engagement' => [function (Customer $customer): bool {
        $customer->update(['birthday' => now()]);

        return resolve(BirthdayEngagement::class)->findRecipients(resolve(TenantSettings::class))->isNotEmpty();
    }],
    'repeat order reminder engagement' => [function (Customer $customer): bool {
        settings(['repeat_reminder_days' => '14']);
        Order::factory()->for($customer)->create([
            'payment_status' => PaymentStatus::Paid,
            'delivery_date' => now()->subDays(30),
        ]);

        return resolve(RepeatOrderReminderEngagement::class)->findRecipients(resolve(TenantSettings::class))->isNotEmpty();
    }],
    'review request engagement' => [function (Customer $customer): bool {
        settings(['review_request_delay_hours' => '24']);
        Order::factory()->for($customer)->create([
            'status' => OrderStatus::Delivered,
            'review_request_sent_at' => null,
            'updated_at' => now()->subHours(48),
        ]);

        return resolve(ReviewRequestEngagement::class)->findRecipients(resolve(TenantSettings::class))->isNotEmpty();
    }],
    'abandoned cart recovery' => [function (Customer $customer): bool {
        runCommandsAsOneTenant();
        settings(['abandoned_cart_recovery_enabled' => '1', 'abandoned_cart_recovery_hours' => '24']);
        $cart = Cart::factory()->withEmail($customer->email)->abandoned(48)->create();
        CartItem::factory()->for($cart)->create();

        artisan('carts:send-abandonment-emails')->assertSuccessful();

        return Mail::queued(AbandonedCartRecoveryMail::class)->isNotEmpty();
    }],
]);

test('a subscribed customer is reached by every marketing sender', function (Closure $reach) {
    $customer = Customer::factory()->create();

    expect($reach($customer))->toBeTrue();
})->with('marketing senders');

test('an opted-out customer is skipped by every marketing sender', function (Closure $reach) {
    $customer = Customer::factory()->unsubscribed()->create();

    expect($reach($customer))->toBeFalse();
})->with('marketing senders');

test('a campaign counts and logs only the customers it actually mails', function () {
    $subscribed = Customer::factory()->create();
    $optedOut = Customer::factory()->unsubscribed()->create();
    foreach ([$subscribed, $optedOut] as $customer) {
        Order::factory()->for($customer)->paid()->create(['delivery_date' => now()->subDays(5)->toDateString()]);
    }
    $campaign = CustomerCampaign::factory()->create(['target_segment' => 'all']);

    $sent = resolve(SendCustomerCampaign::class)($campaign);

    expect($sent)->toBe(1)
        ->and($campaign->fresh()->recipient_count)->toBe(1)
        ->and(CustomerCampaignLog::query()->count())->toBe(1);
    Mail::assertQueued(CustomerCampaignMail::class, fn (CustomerCampaignMail $mail): bool => $mail->hasTo($subscribed->email));
});

test('a bulk message counts only the customers it actually mails', function () {
    $subscribed = Customer::factory()->create();
    $optedOut = Customer::factory()->unsubscribed()->create();

    $outcome = resolve(SendBulkCustomerMessage::class)([$subscribed, $optedOut], BulkMessagePurpose::Promotion, 'Subject', 'Body');

    expect($outcome->sent)->toBe(1);
    Mail::assertQueued(BulkCustomerMessageMail::class, 1);
});

dataset('marketing listeners', [
    'birthday' => [fn (Customer $customer) => resolve(SendHappyBirthdayEmailListener::class)->handle(new CustomerBirthday($customer, null))],
    'repeat order reminder' => [fn (Customer $customer) => resolve(SendRepeatOrderReminderEmailListener::class)->handle(new RepeatOrderReminderDue($customer, 30))],
    'review request' => [fn (Customer $customer) => resolve(SendReviewRequestEmailListener::class)->handle(new ReviewRequested(Order::factory()->for($customer)->create()))],
]);

test('marketing listeners mail a subscribed customer', function (Closure $handle) {
    $customer = Customer::factory()->create();

    $handle($customer);

    Mail::assertOutgoingCount(1);
})->with('marketing listeners');

test('marketing listeners skip a customer who opted out after the event fired', function (Closure $handle) {
    $customer = Customer::factory()->unsubscribed()->create();

    $handle($customer);

    Mail::assertNothingOutgoing();
})->with('marketing listeners');
