<?php

use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['app.kneadit.test'], 'tenancy.tenant_domain' => 'kneadit.test']);
});

function bakeryOnDomain(string $id, array $attributes = []): Tenant
{
    $tenant = Tenant::factory()->create(['id' => $id, ...$attributes]);
    $tenant->domains()->create(['domain' => "{$id}.kneadit.test"]);

    return $tenant;
}

test('a paused bakery refuses API writes with a 403 and creates no order', function () {
    Mail::fake();
    bakeryOnDomain('pausedapi', ['paused_at' => now()]);

    $response = postJson('https://pausedapi.kneadit.test/api/orders', [
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@example.com',
        'delivery_date' => now()->addDays(3)->toDateString(),
        'delivery_type' => 'pickup',
        'items' => [['product_id' => 1, 'quantity' => 1]],
    ]);

    $response->assertForbidden()
        ->assertExactJson(['message' => 'This bakery is not currently accepting orders.']);
    expect(Order::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

test('a paused bakery refuses API reads too', function () {
    bakeryOnDomain('pausedread', ['paused_at' => now()]);

    getJson('https://pausedread.kneadit.test/api/store')
        ->assertForbidden()
        ->assertJsonPath('message', 'This bakery is not currently accepting orders.');
});

test('a bakery that uses its own website keeps its API while it is not paused', function () {
    bakeryOnDomain('externalapi', [
        'storefront_enabled' => false,
        'external_website' => 'https://own-site.example.com',
    ]);

    getJson('https://externalapi.kneadit.test/api/store')->assertSuccessful();
});

test('a paused bakery with a KneadIt storefront shows the paused page instead of its pages', function (string $path) {
    bakeryOnDomain('pausedshop', ['paused_at' => now()]);

    get("https://pausedshop.kneadit.test{$path}")
        ->assertSee('temporarily closed')
        ->assertDontSee('Visit Our Website');
})->with([
    'home page' => ['/'],
    'menu page' => ['/menu'],
]);

test('a running bakery still serves its storefront', function () {
    bakeryOnDomain('runningshop');

    get('https://runningshop.kneadit.test/menu')->assertOk()->assertDontSee('temporarily closed');
});

test('a paused bakery that uses its own website still redirects visitors there', function () {
    bakeryOnDomain('pausedexternal', [
        'paused_at' => now(),
        'storefront_enabled' => false,
        'external_website' => 'https://own-site.example.com',
    ]);

    get('https://pausedexternal.kneadit.test/')->assertRedirect('https://own-site.example.com');
});

test('the admin panel stays reachable for a paused bakery so the owner can subscribe', function () {
    bakeryOnDomain('pausedadmin', ['paused_at' => now()]);

    get('https://pausedadmin.kneadit.test/admin/login')->assertOk();
});
