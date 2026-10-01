<?php

declare(strict_types=1);

use App\Enums\Orders\OrderStatus;
use App\Mail\Customers\AbandonedCartRecoveryMail;
use App\Mail\Customers\BulkCustomerMessageMail;
use App\Mail\Customers\ContactMessageReplyMail;
use App\Mail\Customers\CustomerCampaignMail;
use App\Mail\Customers\CustomerReferralRewardMail;
use App\Mail\Customers\HappyBirthdayMail;
use App\Mail\Customers\ProductAvailableMail;
use App\Mail\Customers\RepeatOrderReminderMail;
use App\Mail\Customers\ReviewRequestMail;
use App\Mail\Marketing\CateringQuoteMail;
use App\Mail\Orders\NewOrderMessageMail;
use App\Mail\Orders\OrderModifiedMail;
use App\Mail\Orders\OrderPlacedMail;
use App\Mail\Orders\OrderStatusMail;
use App\Mail\Orders\OrderTrackingLinkMail;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\ContactMessage;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerReferral;
use App\Models\Engagement\CustomerCampaign;
use App\Models\Financial\Coupon;
use App\Models\Inventory\Product;
use App\Models\Orders\Cart;
use App\Models\Orders\Order;
use App\Models\Orders\OrderMessage;
use App\Services\Customers\MarketingUnsubscribeLinks;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings(['store_name' => 'Test Bakery']);
});

dataset('marketing mailables', [
    'customer campaign' => [fn (Customer $customer) => new CustomerCampaignMail(CustomerCampaign::factory()->create(), $customer, 'TRACKINGTOKEN')],
    'bulk customer message' => [fn (Customer $customer) => new BulkCustomerMessageMail($customer, 'Hello', 'A note for you')],
    'happy birthday' => [fn (Customer $customer) => new HappyBirthdayMail($customer)],
    'repeat order reminder' => [fn (Customer $customer) => new RepeatOrderReminderMail($customer, 30)],
    'abandoned cart recovery' => [fn (Customer $customer) => new AbandonedCartRecoveryMail(Cart::factory()->withEmail($customer->email)->create(), $customer)],
    'review request' => [fn (Customer $customer) => new ReviewRequestMail(Order::factory()->for($customer)->create())],
]);

dataset('transactional mailables', [
    'order placed' => [fn (Customer $customer) => new OrderPlacedMail(Order::factory()->for($customer)->create())],
    'order status' => [fn (Customer $customer) => new OrderStatusMail(Order::factory()->for($customer)->create(), OrderStatus::Confirmed)],
    'order modified' => [fn (Customer $customer) => new OrderModifiedMail(Order::factory()->for($customer)->create(), Money::fromCents(1000), Money::fromCents(2000))],
    'new order message' => [fn (Customer $customer) => new NewOrderMessageMail(OrderMessage::factory()->recycle(Order::factory()->for($customer)->create())->fromBaker()->create())],
    'order tracking link' => [fn (Customer $customer) => new OrderTrackingLinkMail($customer, 'https://example.test/track')],
    'catering quote' => [fn (Customer $customer) => new CateringQuoteMail(CateringInquiry::factory()->create(['customer_email' => $customer->email]))],
    'product available' => [fn (Customer $customer) => new ProductAvailableMail(Product::factory()->create(), $customer->name)],
    'referral reward' => [fn (Customer $customer) => new CustomerReferralRewardMail(
        CustomerReferral::factory()->create(['referrer_customer_id' => $customer->id]),
        Coupon::factory()->create(),
    )],
    'contact message reply' => [fn (Customer $customer) => new ContactMessageReplyMail(ContactMessage::factory()->create(['email' => $customer->email]), 'Thanks!', 'Re: your question')],
]);

test('marketing mails send both one-click unsubscribe headers', function (Closure $makeMail) {
    $customer = Customer::factory()->create();
    $expectedUrl = resolve(MarketingUnsubscribeLinks::class)->unsubscribe($customer);

    $sent = $makeMail($customer)->to($customer->email)->send(Mail::mailer('array'));
    $headers = $sent?->getOriginalMessage()->getHeaders();

    expect($headers?->get('List-Unsubscribe')?->getBodyAsString())->toBe("<{$expectedUrl}>")
        ->and($headers?->get('List-Unsubscribe-Post')?->getBodyAsString())->toBe('List-Unsubscribe=One-Click');
})->with('marketing mailables');

test('marketing mails show a visible unsubscribe link in the footer', function (Closure $makeMail) {
    $customer = Customer::factory()->create();
    $expectedUrl = resolve(MarketingUnsubscribeLinks::class)->unsubscribe($customer);

    $html = $makeMail($customer)->render();

    expect($html)
        ->toContain('Unsubscribe')
        ->toContain(e($expectedUrl))
        ->not->toContain('Reply STOP');
})->with('marketing mailables');

test('transactional mails have neither the headers nor an unsubscribe link', function (Closure $makeMail) {
    $customer = Customer::factory()->create();

    $mail = $makeMail($customer);

    $sent = $mail->to($customer->email)->send(Mail::mailer('array'));
    $headers = $sent?->getOriginalMessage()->getHeaders();

    expect($headers?->has('List-Unsubscribe'))->toBeFalse()
        ->and($headers?->has('List-Unsubscribe-Post'))->toBeFalse()
        ->and(strtolower($mail->render()))->not->toContain('unsubscribe');
})->with('transactional mailables');
