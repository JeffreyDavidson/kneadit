<?php

use App\Enums\Engagement\LoyaltyPointType;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Tenancy;

use function Pest\Laravel\artisan;

/**
 * Production shape: the central database has no tenant tables. The Tenancy
 * double stands in for the database switch by creating the tenant tables on
 * first use (then running $seed) and writing the entering tenant's PayPal
 * credentials.
 *
 * @param  array<string, array<string, string>>  $credentials  tenant id => settings
 */
function paypalTenants(array $credentials, Closure $seed): void
{
    setUpCentralOnlyTest();

    foreach (array_keys($credentials) as $id) {
        createTenant(['id' => $id, 'email' => "{$id}@example.com"]);
    }

    $tenancy = Mockery::mock(Tenancy::class);
    $tenancy->shouldReceive('initialize')->andReturnUsing(function ($tenant) use ($credentials, $seed): void {
        if (! Schema::hasTable('settings')) {
            createTenantTablesOnce();
            $seed();
        }

        settings($credentials[$tenant->id]);
    });
    $tenancy->shouldReceive('end');
    app()->instance(Tenancy::class, $tenancy);
}

function fakePayPal(string $invoiceStatus): void
{
    Http::fake([
        '*/v1/oauth2/token' => Http::response(['access_token' => 'token']),
        '*/v2/invoicing/invoices/*' => Http::response(['status' => $invoiceStatus]),
    ]);
}

test('a paid invoice marks the order paid with the tenant credentials while a tenant without PayPal is skipped', function () {
    paypalTenants([
        'with-paypal' => ['paypal_client_id' => 'client-a', 'paypal_client_secret' => 'secret-a'],
        'without-paypal' => [],
    ], fn () => test()->order = Order::factory()->create(['payment_status' => PaymentStatus::Unpaid, 'paypal_invoice_id' => 'INV-1']));
    fakePayPal('PAID');

    artisan('paypal:check-payments')->assertSuccessful();

    expect(test()->order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
    Http::assertSentCount(2);
});

test('each tenant authenticates with its own PayPal credentials', function () {
    paypalTenants([
        'bakery-a' => ['paypal_client_id' => 'client-a', 'paypal_client_secret' => 'secret-a'],
        'bakery-b' => ['paypal_client_id' => 'client-b', 'paypal_client_secret' => 'secret-b'],
    ], fn () => Order::factory()->create(['payment_status' => PaymentStatus::Unpaid, 'paypal_invoice_id' => 'INV-1']));
    fakePayPal('SENT');

    artisan('paypal:check-payments')->assertSuccessful();

    $authorizations = Http::recorded(fn (Request $request): bool => str_ends_with($request->url(), '/v1/oauth2/token'))
        ->map(fn (array $pair): string => base64_decode(str_replace('Basic ', '', $pair[0]->header('Authorization')[0])))
        ->values()
        ->all();
    expect($authorizations)->toBe(['client-a:secret-a', 'client-b:secret-b']);
});

test('a tenant whose PayPal call fails does not stop the next tenant', function () {
    paypalTenants([
        'bakery-a' => ['paypal_client_id' => 'client-a', 'paypal_client_secret' => 'secret-a'],
        'bakery-b' => ['paypal_client_id' => 'client-b', 'paypal_client_secret' => 'secret-b'],
    ], fn () => test()->order = Order::factory()->create(['payment_status' => PaymentStatus::Unpaid, 'paypal_invoice_id' => 'INV-1']));
    Http::fake([
        '*/v1/oauth2/token' => fn (Request $request) => str_contains(base64_decode(str_replace('Basic ', '', $request->header('Authorization')[0])), 'client-a')
            ? Http::response(['error' => 'invalid_client'], 401)
            : Http::response(['access_token' => 'token']),
        '*/v2/invoicing/invoices/*' => Http::response(['status' => 'PAID']),
    ]);

    artisan('paypal:check-payments')->assertSuccessful();

    expect(test()->order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
});

test('a refunded invoice takes back the points the order earned', function () {
    paypalTenants([
        'with-paypal' => ['paypal_client_id' => 'client-a', 'paypal_client_secret' => 'secret-a'],
    ], function (): void {
        $customer = Customer::factory()->create();
        test()->order = Order::factory()->for($customer)->create(['payment_status' => PaymentStatus::Unpaid, 'paypal_invoice_id' => 'INV-1']);
        LoyaltyPoint::factory()->for($customer)->earned(120)->create(['order_id' => test()->order->id]);
    });
    fakePayPal('REFUNDED');

    artisan('paypal:check-payments')->assertSuccessful();

    expect(test()->order->refresh()->payment_status)->toBe(PaymentStatus::Refunded)
        ->and(LoyaltyPoint::query()->forOrder(test()->order)->where('type', LoyaltyPointType::Reversed)->sole()->points)->toBe(120);
});

test('a bakery with no PayPal credentials of its own is skipped even when the platform has some', function () {
    config([
        'services.paypal.client_id' => 'platform-client-id',
        'services.paypal.client_secret' => 'platform-client-secret',
    ]);
    paypalTenants([
        'platform-only' => [],
    ], fn () => Order::factory()->create(['payment_status' => PaymentStatus::Unpaid, 'paypal_invoice_id' => 'INV-1']));
    fakePayPal('PAID');

    artisan('paypal:check-payments')->assertSuccessful();

    Http::assertNothingSent();
});

test('a bakery is polled with its own credentials when the platform has none', function () {
    config([
        'services.paypal.client_id' => null,
        'services.paypal.client_secret' => null,
    ]);
    paypalTenants([
        'own-credentials' => ['paypal_client_id' => 'client-a', 'paypal_client_secret' => 'secret-a'],
    ], fn () => test()->order = Order::factory()->create(['payment_status' => PaymentStatus::Unpaid, 'paypal_invoice_id' => 'INV-1']));
    fakePayPal('PAID');

    artisan('paypal:check-payments')->assertSuccessful();

    expect(test()->order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
});

test('each bakery is polled on the PayPal environment its own sandbox setting names', function (string $sandbox, string $host) {
    config(['services.paypal.sandbox' => true]);
    paypalTenants([
        'own-sandbox' => ['paypal_client_id' => 'client-a', 'paypal_client_secret' => 'secret-a', 'paypal_sandbox' => $sandbox],
    ], fn () => Order::factory()->create(['payment_status' => PaymentStatus::Unpaid, 'paypal_invoice_id' => 'INV-1']));
    fakePayPal('SENT');

    artisan('paypal:check-payments')->assertSuccessful();

    Http::assertSent(fn (Request $request): bool => parse_url($request->url(), PHP_URL_HOST) === $host);
    Http::assertNotSent(fn (Request $request): bool => parse_url($request->url(), PHP_URL_HOST) !== $host);
})->with([
    'sandbox on' => ['1', 'api-m.sandbox.paypal.com'],
    'sandbox off' => ['0', 'api-m.paypal.com'],
]);

function cancelledOrderTenant(string $invoiceStatus, ?Closure $tweak = null): void
{
    paypalTenants([
        'cancelled-bakery' => ['paypal_client_id' => 'client-a', 'paypal_client_secret' => 'secret-a'],
    ], function () use ($tweak): void {
        test()->owner = User::factory()->owner()->create();
        test()->order = Order::factory()->cancelled()->create(['payment_status' => PaymentStatus::Unpaid, 'paypal_invoice_id' => 'INV-CANCELLED']);
        $tweak?->__invoke(test()->order);
    });
    fakePayPal($invoiceStatus);
}

test('an invoice paid after its order was cancelled is not marked paid and the owners are told to refund it', function () {
    cancelledOrderTenant('PAID');

    artisan('paypal:check-payments')->assertSuccessful();

    expect(test()->order->refresh())
        ->payment_status->toBe(PaymentStatus::Unpaid)
        ->status->toBe(OrderStatus::Cancelled)
        ->and(test()->owner->notifications()->count())->toBe(1)
        ->and(test()->owner->notifications()->first()->data['body'])->toContain('INV-CANCELLED');
});

test('the owners hear about a late PayPal payment once, not every hour', function () {
    cancelledOrderTenant('PAID');

    artisan('paypal:check-payments')->assertSuccessful();
    artisan('paypal:check-payments')->assertSuccessful();

    expect(test()->owner->notifications()->count())->toBe(1);
});

test('a cancelled order whose invoice is cancelled is left alone', function (string $invoiceStatus) {
    cancelledOrderTenant($invoiceStatus);

    artisan('paypal:check-payments')->assertSuccessful();

    expect(test()->order->refresh())
        ->payment_status->toBe(PaymentStatus::Unpaid)
        ->status->toBe(OrderStatus::Cancelled)
        ->and(test()->owner->notifications()->count())->toBe(0);
})->with(['CANCELLED', 'SENT', 'REFUNDED']);

test('a cancelled order from long ago is no longer polled', function () {
    cancelledOrderTenant('PAID', fn (Order $order) => Order::query()->whereKey($order)->toBase()->update(['updated_at' => now()->subDays(60)]));

    artisan('paypal:check-payments')->assertSuccessful();

    Http::assertNothingSent();
    expect(test()->owner->notifications()->count())->toBe(0);
});
