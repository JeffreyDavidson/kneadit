<?php

use App\Models\Inventory\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function normalizeCustomerEmailMigration(): Migration
{
    $migration = require database_path('migrations/tenant/2026_10_01_140000_normalize_customer_email_case.php');

    throw_unless($migration instanceof Migration, RuntimeException::class, 'Expected a Laravel migration.');

    return $migration;
}

function runNormalizeCustomerEmailMigration(): void
{
    $up = [normalizeCustomerEmailMigration(), 'up'];

    throw_unless(is_callable($up), RuntimeException::class, 'Expected a runnable Laravel migration.');

    $up();
}

function insertRawCustomer(string $email): int
{
    return DB::table('customers')->insertGetId([
        'name' => 'Raw Customer',
        'email' => $email,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('lowercases and trims mixed-case customer emails', function () {
    $mixed = insertRawCustomer('Alice@Example.COM');
    $padded = insertRawCustomer('  bob@example.com ');
    $clean = insertRawCustomer('carol@example.com');

    runNormalizeCustomerEmailMigration();

    expect(DB::table('customers')->where('id', $mixed)->value('email'))->toBe('alice@example.com')
        ->and(DB::table('customers')->where('id', $padded)->value('email'))->toBe('bob@example.com')
        ->and(DB::table('customers')->where('id', $clean)->value('email'))->toBe('carol@example.com');
});

test('lowercases the other customer email columns', function () {
    $product = Product::factory()->create();
    $cart = DB::table('carts')->insertGetId([
        'cart_token' => 'token-1',
        'customer_email' => 'Dana@Example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $nullCart = DB::table('carts')->insertGetId([
        'cart_token' => 'token-2',
        'customer_email' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $favorite = DB::table('customer_favorites')->insertGetId([
        'customer_email' => 'Dana@Example.com',
        'product_id' => $product->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    runNormalizeCustomerEmailMigration();

    expect(DB::table('carts')->where('id', $cart)->value('customer_email'))->toBe('dana@example.com')
        ->and(DB::table('carts')->where('id', $nullCart)->value('customer_email'))->toBeNull()
        ->and(DB::table('customer_favorites')->where('id', $favorite)->value('customer_email'))->toBe('dana@example.com');
});

test('leaves a case-only duplicate pair unchanged and logs it', function () {
    $logger = Log::spy();
    $upper = insertRawCustomer('Erin@Example.com');
    $lower = insertRawCustomer('erin@example.com');
    $other = insertRawCustomer('Frank@Example.com');

    runNormalizeCustomerEmailMigration();

    expect(DB::table('customers')->where('id', $upper)->value('email'))->toBe('Erin@Example.com')
        ->and(DB::table('customers')->where('id', $lower)->value('email'))->toBe('erin@example.com')
        ->and(DB::table('customers')->where('id', $other)->value('email'))->toBe('frank@example.com');

    $logger->shouldHaveReceived('warning')->once()->withArgs(
        fn (string $message, array $context): bool => $context['customer_ids'] === [$upper, $lower]
            && $context['emails'] === ['Erin@Example.com', 'erin@example.com']
            && array_key_exists('tenant_id', $context),
    );
});

test('leaves favorites that would collide after lowercasing unchanged and logs them', function () {
    $logger = Log::spy();
    $product = Product::factory()->create();
    $upper = DB::table('customer_favorites')->insertGetId([
        'customer_email' => 'Gina@Example.com',
        'product_id' => $product->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $lower = DB::table('customer_favorites')->insertGetId([
        'customer_email' => 'gina@example.com',
        'product_id' => $product->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    runNormalizeCustomerEmailMigration();

    expect(DB::table('customer_favorites')->where('id', $upper)->value('customer_email'))->toBe('Gina@Example.com')
        ->and(DB::table('customer_favorites')->where('id', $lower)->value('customer_email'))->toBe('gina@example.com');

    $logger->shouldHaveReceived('warning')->once();
});

test('running the migration twice changes nothing the second time', function () {
    Log::spy();
    insertRawCustomer('Hank@Example.com');
    insertRawCustomer('Iris@Example.com');
    insertRawCustomer('iris@example.com');

    runNormalizeCustomerEmailMigration();
    $afterFirst = DB::table('customers')->orderBy('id')->pluck('email')->all();

    runNormalizeCustomerEmailMigration();

    expect(DB::table('customers')->orderBy('id')->pluck('email')->all())->toBe($afterFirst)
        ->and($afterFirst)->toBe(['hank@example.com', 'Iris@Example.com', 'iris@example.com']);
});
