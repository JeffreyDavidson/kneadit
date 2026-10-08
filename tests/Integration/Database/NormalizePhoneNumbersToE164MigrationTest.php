<?php

use App\Models\Customers\CateringInquiry;
use App\Models\Customers\Customer;
use App\Models\Customers\WaitlistEntry;
use App\Models\Inventory\Supplier;
use App\Models\Orders\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function runNormalizePhoneNumbersMigration(): void
{
    $migration = require database_path('migrations/tenant/2026_10_08_120000_normalize_phone_numbers_to_e164.php');

    throw_unless($migration instanceof Migration, RuntimeException::class, 'Expected a Laravel migration.');

    $up = [$migration, 'up'];

    throw_unless(is_callable($up), RuntimeException::class, 'Expected a runnable Laravel migration.');

    $up();
}

/** Writes the phone as it sits in the database today, past the cast. */
function storeRawCustomerPhone(?string $phone): int
{
    $customer = Customer::factory()->create();

    DB::table('customers')->where('id', $customer->id)->update(['phone' => $phone]);

    return $customer->id;
}

test('rewrites stored customer phones in E.164, reading numbers without a country code as US', function (string $stored, string $expected) {
    $id = storeRawCustomerPhone($stored);

    runNormalizePhoneNumbersMigration();

    expect(DB::table('customers')->where('id', $id)->value('phone'))->toBe($expected);
})->with([
    'digits kept by the old cast' => ['9133877359', '+19133877359'],
    'digits with the leading 1' => ['19133877359', '+19133877359'],
    'formatted' => ['(913) 387-7359', '+19133877359'],
    'already E.164' => ['+19133877359', '+19133877359'],
    'UK with its country code' => ['+44 20 7946 0958', '+442079460958'],
]);

test('leaves values that are not complete numbers exactly as they are and logs their rows', function (string $stored) {
    $logger = Log::spy();
    $id = storeRawCustomerPhone($stored);

    runNormalizePhoneNumbersMigration();

    expect(DB::table('customers')->where('id', $id)->value('phone'))->toBe($stored);
    $logger->shouldHaveReceived('warning')->once()->withArgs(
        fn (string $message, array $context): bool => $context['table'] === 'customers' && $context['ids'] === [$id],
    );
})->with([
    'local seven digits' => ['5550100'],
    'UK digits whose plus the old cast dropped' => ['442079460958'],
    'letters' => ['ask at the counter'],
    'with an extension' => ['913-387-7359 x12'],
]);

test('does not touch empty phones', function (?string $stored) {
    $id = storeRawCustomerPhone($stored);

    runNormalizePhoneNumbersMigration();

    expect(DB::table('customers')->where('id', $id)->value('phone'))->toBe($stored);
})->with([
    'null' => [null],
    'empty string' => [''],
]);

test('rewrites the other phone columns', function () {
    $supplier = Supplier::factory()->create();
    $waitlistEntry = WaitlistEntry::factory()->create();
    $inquiry = CateringInquiry::factory()->create();
    $order = Order::factory()->create();
    DB::table('suppliers')->where('id', $supplier->id)->update(['phone' => '913.387.7359']);
    DB::table('waitlist_entries')->where('id', $waitlistEntry->id)->update(['customer_phone' => '9133877359']);
    DB::table('catering_inquiries')->where('id', $inquiry->id)->update(['customer_phone' => '913-387-7359']);
    DB::table('orders')->where('id', $order->id)->update(['pickup_contact_phone' => '(913) 387-7359']);

    runNormalizePhoneNumbersMigration();

    expect(DB::table('suppliers')->where('id', $supplier->id)->value('phone'))->toBe('+19133877359')
        ->and(DB::table('waitlist_entries')->where('id', $waitlistEntry->id)->value('customer_phone'))->toBe('+19133877359')
        ->and(DB::table('catering_inquiries')->where('id', $inquiry->id)->value('customer_phone'))->toBe('+19133877359')
        ->and(DB::table('orders')->where('id', $order->id)->value('pickup_contact_phone'))->toBe('+19133877359');
});

test('rewrites the store phone setting', function (string $stored, string $expected) {
    settings(['store_phone' => $stored]);

    runNormalizePhoneNumbersMigration();

    expect(DB::table('settings')->where('key', 'store_phone')->value('value'))->toBe($expected);
})->with([
    'formatted US' => ['(863) 555-0123', '+18635550123'],
    'unreadable is kept' => ['555-0123', '555-0123'],
]);

test('is safe to run twice', function () {
    $id = storeRawCustomerPhone('(913) 387-7359');

    runNormalizePhoneNumbersMigration();
    runNormalizePhoneNumbersMigration();

    expect(DB::table('customers')->where('id', $id)->value('phone'))->toBe('+19133877359');
});
