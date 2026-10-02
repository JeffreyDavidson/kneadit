<?php

declare(strict_types=1);

use App\Models\Customers\Customer;
use App\Models\Platform\Tenant;
use App\Services\Customers\MarketingUnsubscribeLinks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

afterEach(function () {
    tenancy()->tenant = null;
});

test('builds a non-expiring signed unsubscribe link for the customer', function () {
    $customer = Customer::factory()->create();

    $url = resolve(MarketingUnsubscribeLinks::class)->unsubscribe($customer);

    expect($url)
        ->toContain("/email/unsubscribe/{$customer->id}")
        ->toContain('signature=')
        ->not->toContain('expires=');
});

test('the signature holds whichever host serves the link', function () {
    $customer = Customer::factory()->create();
    $url = resolve(MarketingUnsubscribeLinks::class)->unsubscribe($customer);
    $pathAndQuery = parse_url($url, PHP_URL_PATH).'?'.parse_url($url, PHP_URL_QUERY);

    $request = Request::create("https://sunrise.example.test{$pathAndQuery}");

    expect($request->hasValidRelativeSignature())->toBeTrue();
});

test('points at the bakery storefront host when a tenant is active', function () {
    config(['app.url' => 'https://app.example.test', 'tenancy.tenant_domain' => 'example.test']);
    tenancy()->tenant = new Tenant(['id' => 'sunrise']);
    $customer = Customer::factory()->create();

    $url = resolve(MarketingUnsubscribeLinks::class)->unsubscribe($customer);

    expect($url)->toStartWith("https://sunrise.example.test/email/unsubscribe/{$customer->id}?signature=");
});
