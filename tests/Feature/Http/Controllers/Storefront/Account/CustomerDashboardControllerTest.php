<?php

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerFavorite;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('dashboard renders with empty state for a fresh customer', function () {
    $customer = Customer::factory()->verified()->withPassword()->create();

    actingAs($customer, 'customer');

    withoutMiddleware(tenantMiddleware())
        ->get(route('account.dashboard', [], false))
        ->assertOk()
        ->assertViewIs('storefront.account.dashboard')
        ->assertViewHas('customer', fn (Customer $passed) => $passed->is($customer))
        ->assertViewHas('orders', fn (Collection $orders): bool => $orders->isEmpty())
        ->assertViewHas('favorites', fn (Collection $favorites): bool => $favorites->isEmpty());
});

test('dashboard surfaces a customer\'s recent orders (capped at 10, latest first)', function () {
    $customer = Customer::factory()->verified()->withPassword()->create();
    Order::factory()
        ->for($customer)
        ->count(12)
        ->state(new Illuminate\Database\Eloquent\Factories\Sequence(...array_map(
            fn (int $i) => ['created_at' => now()->subDays($i)],
            range(0, 11),
        )))
        ->create();

    actingAs($customer, 'customer');

    withoutMiddleware(tenantMiddleware())
        ->get(route('account.dashboard', [], false))
        ->assertOk()
        ->assertViewHas('orders', fn (Collection $orders): bool => $orders->count() === 10);
});

test('dashboard surfaces favorites scoped to the customer\'s email', function () {
    $customer = Customer::factory()->verified()->withPassword()->create(['email' => 'alice@example.com']);
    $product = Product::factory()->create();

    CustomerFavorite::factory()->create([
        'customer_email' => 'alice@example.com',
        'product_id' => $product->id,
    ]);
    CustomerFavorite::factory()->create([
        'customer_email' => 'someone-else@example.com',
        'product_id' => $product->id,
    ]);

    actingAs($customer, 'customer');

    withoutMiddleware(tenantMiddleware())
        ->get(route('account.dashboard', [], false))
        ->assertOk()
        ->assertViewHas('favorites', fn (Collection $favorites): bool => $favorites->count() === 1
            && $favorites->contains(fn (CustomerFavorite $favorite): bool => $favorite->customer_email === 'alice@example.com'));
});

test('referralShareUrl is null when the referral program is disabled', function () {
    settings(['customer_referral_program_enabled' => false]);

    $customer = Customer::factory()->verified()->withPassword()->create();

    actingAs($customer, 'customer');

    withoutMiddleware(tenantMiddleware())
        ->get(route('account.dashboard', [], false))
        ->assertOk()
        ->assertViewHas('referralCode', null)
        ->assertViewHas('referralShareUrl', null);
});
