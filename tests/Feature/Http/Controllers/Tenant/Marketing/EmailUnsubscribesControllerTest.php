<?php

declare(strict_types=1);

use App\Models\Customers\Customer;
use App\Services\Customers\MarketingUnsubscribeLinks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings(['store_name' => 'Rise & Shine Bakery']);
});

test('a signed GET shows the confirmation page without unsubscribing anyone', function () {
    $customer = Customer::factory()->create();
    $url = resolve(MarketingUnsubscribeLinks::class)->unsubscribe($customer);

    $response = withoutMiddleware(tenantMiddleware())->get($url);

    $response->assertOk();
    $response->assertSee('Unsubscribe');
    $response->assertSeeHtml('Rise &amp; Shine Bakery');
    expect($customer->fresh()->marketing_opted_out_at)->toBeNull();
});

test('a signed POST unsubscribes the customer immediately', function () {
    Date::setTestNow('2026-10-01 12:00');
    $customer = Customer::factory()->create();
    $url = resolve(MarketingUnsubscribeLinks::class)->unsubscribe($customer);

    $response = withoutMiddleware(tenantMiddleware())->post($url);

    $response->assertOk();
    $response->assertSee("You've been unsubscribed from Rise & Shine Bakery marketing emails");
    expect($customer->fresh()->marketing_opted_out_at?->toIso8601String())->toBe(now()->toIso8601String());
});

test('unsubscribing twice keeps the original opt-out time', function () {
    $customer = Customer::factory()->unsubscribed()->create(['marketing_opted_out_at' => now()->subWeek()]);
    $original = $customer->fresh()->marketing_opted_out_at;
    $url = resolve(MarketingUnsubscribeLinks::class)->unsubscribe($customer);

    withoutMiddleware(tenantMiddleware())->post($url)->assertOk();

    expect($customer->fresh()->marketing_opted_out_at?->toIso8601String())->toBe($original?->toIso8601String());
});

test('the unsubscribed page offers a re-subscribe button', function () {
    $customer = Customer::factory()->unsubscribed()->create();
    $url = resolve(MarketingUnsubscribeLinks::class)->unsubscribe($customer);

    $response = withoutMiddleware(tenantMiddleware())->get($url);

    $response->assertOk();
    $response->assertSee('Re-subscribe');
});

test('re-subscribing clears the opt-out', function () {
    $customer = Customer::factory()->unsubscribed()->create();
    $url = resolve(MarketingUnsubscribeLinks::class)->unsubscribe($customer);

    $response = withoutMiddleware(tenantMiddleware())->delete($url);

    $response->assertOk();
    $response->assertSee('subscribed again');
    expect($customer->fresh()->marketing_opted_out_at)->toBeNull();
});

test('the one-click POST works without a CSRF token', function () {
    // The test runner skips CSRF checks, so pretend to be production to prove the route opts out of them.
    app()->detectEnvironment(fn (): string => 'production');
    $customer = Customer::factory()->create();
    $url = resolve(MarketingUnsubscribeLinks::class)->unsubscribe($customer);

    $control = withoutMiddleware(tenantMiddleware())->post(route('cart.persist'));
    $response = withoutMiddleware(tenantMiddleware())->post($url, ['List-Unsubscribe' => 'One-Click']);

    $control->assertStatus(419);
    $response->assertOk();
    expect($customer->fresh()->marketing_opted_out_at)->not->toBeNull();
});

test('an unsigned or tampered URL is rejected', function (string $method) {
    $customer = Customer::factory()->create();
    $signed = resolve(MarketingUnsubscribeLinks::class)->unsubscribe($customer);
    $other = Customer::factory()->create();

    $unsigned = route('emailUnsubscribe.show', ['customer' => $customer], absolute: false);
    $tampered = str_replace("/{$customer->id}?", "/{$other->id}?", $signed);

    withoutMiddleware(tenantMiddleware())->call($method, $unsigned)->assertForbidden();
    withoutMiddleware(tenantMiddleware())->call($method, $tampered)->assertForbidden();
    expect($customer->fresh()->marketing_opted_out_at)->toBeNull()
        ->and($other->fresh()->marketing_opted_out_at)->toBeNull();
})->with(['GET', 'POST', 'DELETE']);
