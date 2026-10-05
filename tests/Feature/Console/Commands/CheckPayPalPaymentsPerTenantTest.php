<?php

use App\Enums\Engagement\LoyaltyPointType;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
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

beforeEach(function () {
    config([
        'services.paypal.client_id' => 'platform-client-id',
        'services.paypal.client_secret' => 'platform-client-secret',
    ]);
});

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
