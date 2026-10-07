<?php

use App\Actions\Orders\CancelOrder;
use App\Enums\Financial\GiftCardTransactionType;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Exceptions\Orders\InvalidOrderTransitionException;
use App\Exceptions\Orders\OrderRefundInProgressException;
use App\Exceptions\Stripe\StripeRefundFailedException;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use App\Models\Financial\Refund;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Stripe\Exception\InvalidRequestException;
use Tests\Support\Stripe\FakeCheckoutStripeClient;
use Tests\Support\Stripe\FakeRefundStripeClient;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('cancelling an unpaid order does not touch Stripe', function () {
    $stripe = FakeRefundStripeClient::untouched();
    $order = Order::factory()->confirmed()->unpaid()->create();

    $refund = resolve(CancelOrder::class)($order);

    expect($refund)->toBeNull()
        ->and($order->refresh())
        ->status->toBe(OrderStatus::Cancelled)
        ->payment_status->toBe(PaymentStatus::Unpaid);
    $stripe->unused();
});

test('cancelling a paid Stripe order refunds it once and cancels it', function () {
    $stripe = FakeRefundStripeClient::succeeding('re_cancel_paid');
    $manager = User::factory()->manager()->create();
    $order = Order::factory()->confirmed()->paid()->create([
        'stripe_payment_intent_id' => 'pi_cancel_paid',
        'total' => 25.00,
    ]);

    $refund = resolve(CancelOrder::class)($order, $manager, 'Customer asked');

    expect($refund)->toBeInstanceOf(Refund::class)
        ->and($refund->stripe_refund_id)->toBe('re_cancel_paid')
        ->and($refund->reason)->toBe('Customer asked')
        ->and($refund->user_id)->toBe($manager->id)
        ->and($order->refresh())
        ->status->toBe(OrderStatus::Cancelled)
        ->payment_status->toBe(PaymentStatus::Refunded);
    $stripe->verify();
});

test('a Stripe refusal leaves the order uncancelled and paid', function () {
    $stripe = FakeRefundStripeClient::refusing();
    $order = Order::factory()->confirmed()->paid()->create(['stripe_payment_intent_id' => 'pi_cancel_refused']);

    expect(fn () => resolve(CancelOrder::class)($order))->toThrow(StripeRefundFailedException::class)
        ->and($order->refresh())->status->toBe(OrderStatus::Confirmed)->payment_status->toBe(PaymentStatus::Paid)
        ->and(Refund::query()->count())->toBe(0);
    $stripe->verify();
});

test('cancelling a paid order that was not paid through Stripe cancels it and leaves it paid for a manual refund', function () {
    $stripe = FakeRefundStripeClient::untouched();
    $order = Order::factory()->confirmed()->paid()->create(['stripe_payment_intent_id' => null]);

    $refund = resolve(CancelOrder::class)($order);

    expect($refund)->toBeNull()
        ->and($order->refresh())
        ->status->toBe(OrderStatus::Cancelled)
        ->payment_status->toBe(PaymentStatus::Paid);
    $stripe->unused();
});

test('an order that cannot be cancelled is not refunded', function () {
    $stripe = FakeRefundStripeClient::untouched();
    $order = Order::factory()->ready()->paid()->create(['stripe_payment_intent_id' => 'pi_cancel_ready']);

    expect(fn () => resolve(CancelOrder::class)($order))->toThrow(InvalidOrderTransitionException::class)
        ->and($order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
    $stripe->unused();
});

test('cancelling the same paid order twice from a stale copy refunds it once', function () {
    $stripe = FakeRefundStripeClient::succeeding('re_cancel_twice');
    $order = Order::factory()->confirmed()->paid()->create(['stripe_payment_intent_id' => 'pi_cancel_twice']);
    $stale = Order::query()->findOrFail($order->id);

    resolve(CancelOrder::class)($order);

    expect(fn () => resolve(CancelOrder::class)($stale))->toThrow(InvalidOrderTransitionException::class)
        ->and(Refund::query()->count())->toBe(1);
    $stripe->verify();
});

test('cancelling an order whose refund another request is making is refused without a Stripe call', function () {
    Date::setTestNow('2026-10-05 12:00');
    $stripe = FakeRefundStripeClient::untouched();
    $order = Order::factory()->confirmed()->paid()->create([
        'stripe_payment_intent_id' => 'pi_cancel_claimed',
        'refund_claimed_at' => '2026-10-05 11:58:00',
    ]);

    expect(fn () => resolve(CancelOrder::class)($order))->toThrow(OrderRefundInProgressException::class)
        ->and($order->refresh())
        ->status->toBe(OrderStatus::Confirmed)
        ->payment_status->toBe(PaymentStatus::Paid);
    $stripe->unused();
});

test('cancelling an unpaid order expires its open Stripe checkout session so it cannot be paid later', function () {
    settings(['stripe_connect_id' => 'acct_test']);
    $sessions = FakeCheckoutStripeClient::bind();
    $sessions->expects('expire')->with('cs_cancel_open', [], ['stripe_account' => 'acct_test']);
    $order = Order::factory()->confirmed()->unpaid()->create(['stripe_checkout_session_id' => 'cs_cancel_open']);

    resolve(CancelOrder::class)($order);

    expect($order->refresh())
        ->status->toBe(OrderStatus::Cancelled)
        ->payment_status->toBe(PaymentStatus::Unpaid);
});

test('a refused session expiry does not stop an unpaid order being cancelled', function () {
    settings(['stripe_connect_id' => 'acct_test']);
    $sessions = FakeCheckoutStripeClient::bind();
    $sessions->expects('expire')->throws(InvalidRequestException::factory('Session already complete.', 400, null, null));
    $order = Order::factory()->confirmed()->unpaid()->create(['stripe_checkout_session_id' => 'cs_cancel_done']);

    resolve(CancelOrder::class)($order);

    expect($order->refresh()->status)->toBe(OrderStatus::Cancelled);
});

test('an order that cannot be cancelled keeps its checkout session open', function () {
    settings(['stripe_connect_id' => 'acct_test']);
    $sessions = FakeCheckoutStripeClient::bind();
    $sessions->expects('expire')->never();
    $order = Order::factory()->ready()->unpaid()->create(['stripe_checkout_session_id' => 'cs_cancel_ready']);

    expect(fn () => resolve(CancelOrder::class)($order))->toThrow(InvalidOrderTransitionException::class);
});

test('cancelling credits a gift card with what was redeemed even if the order amount was tampered with', function () {
    FakeRefundStripeClient::untouched();
    $giftCard = GiftCard::factory()->create(['initial_balance' => 10.00, 'current_balance' => 0.00]);
    $order = Order::factory()->confirmed()->unpaid()->create([
        'gift_card_id' => $giftCard->id,
        'gift_card_amount' => 50.00,
    ]);
    GiftCardTransaction::factory()->redemption()->create([
        'gift_card_id' => $giftCard->id,
        'order_id' => $order->id,
        'amount' => -10.00,
    ]);

    resolve(CancelOrder::class)($order);

    expect($giftCard->refresh()->current_balance->dollars())->toBe(10.00)
        ->and(GiftCardTransaction::query()->where('order_id', $order->id)->where('type', GiftCardTransactionType::Refund)->count())->toBe(1);
});

function bakeryPaypalCredentials(): void
{
    settings([
        'paypal_client_id' => 'bakery-id',
        'paypal_client_secret' => 'bakery-secret',
    ]);
}

test('cancelling an unpaid order cancels its open PayPal invoice so it cannot be paid later', function () {
    bakeryPaypalCredentials();
    Http::preventStrayRequests();
    Http::fake([
        '*/v1/oauth2/token' => Http::response(['access_token' => 'token']),
        '*/v2/invoicing/invoices/INV-CANCEL-1/cancel' => Http::response([], 204),
    ]);
    $order = Order::factory()->confirmed()->unpaid()->create(['paypal_invoice_id' => 'INV-CANCEL-1']);

    resolve(CancelOrder::class)($order);

    expect($order->refresh())
        ->status->toBe(OrderStatus::Cancelled)
        ->paypal_invoice_id->toBe('INV-CANCEL-1');
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v2/invoicing/invoices/INV-CANCEL-1/cancel'));
});

test('a PayPal failure does not stop an unpaid order being cancelled', function () {
    bakeryPaypalCredentials();
    Http::preventStrayRequests();
    Http::fake([
        '*/v1/oauth2/token' => Http::response(['access_token' => 'token']),
        '*/v2/invoicing/invoices/INV-CANCEL-2/cancel' => Http::response(['name' => 'INVALID_INVOICE_STATE'], 422),
    ]);
    $order = Order::factory()->confirmed()->unpaid()->create(['paypal_invoice_id' => 'INV-CANCEL-2']);

    resolve(CancelOrder::class)($order);

    expect($order->refresh()->status)->toBe(OrderStatus::Cancelled);
});

test('cancelling an order without PayPal credentials still cancels it', function () {
    Http::preventStrayRequests();
    $order = Order::factory()->confirmed()->unpaid()->create(['paypal_invoice_id' => 'INV-CANCEL-3']);

    resolve(CancelOrder::class)($order);

    expect($order->refresh()->status)->toBe(OrderStatus::Cancelled);
    Http::assertNothingSent();
});

test('cancelling an order with no PayPal invoice, or one that is already paid, does not call PayPal', function (?string $invoiceId, string $state) {
    FakeRefundStripeClient::untouched();
    bakeryPaypalCredentials();
    Http::preventStrayRequests();
    $order = Order::factory()->confirmed()->{$state}()->create(['paypal_invoice_id' => $invoiceId, 'stripe_payment_intent_id' => null]);

    resolve(CancelOrder::class)($order);

    expect($order->refresh()->status)->toBe(OrderStatus::Cancelled);
    Http::assertNothingSent();
})->with([
    'unpaid, no invoice' => [null, 'unpaid'],
    'paid by PayPal invoice' => ['INV-CANCEL-4', 'paid'],
]);

test('an order that cannot be cancelled keeps its PayPal invoice open', function () {
    bakeryPaypalCredentials();
    Http::preventStrayRequests();
    $order = Order::factory()->ready()->unpaid()->create(['paypal_invoice_id' => 'INV-CANCEL-5']);

    expect(fn () => resolve(CancelOrder::class)($order))->toThrow(InvalidOrderTransitionException::class);
    Http::assertNothingSent();
});
