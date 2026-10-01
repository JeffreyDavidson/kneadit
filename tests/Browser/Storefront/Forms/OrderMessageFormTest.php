<?php

use App\Console\Commands\Tenants\ProvisionTestTenantCommand;
use App\Models\Customers\Customer;
use App\Models\Platform\Tenant;
use Database\Seeders\BrowserTestFixtureSeeder;
use Illuminate\Support\Facades\URL;

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

// The order tracking page loads and sends order messages over the
// {data: ...} API envelope. The fixture RFM customer has four orders, and
// opening the signed tracking link grants this browser session access to
// them (the email click-through itself is covered by the feature tests).
//
// The link is built for the browser-test tenant domain over https — Herd
// 301-redirects http to https, which would invalidate the signature, so URL
// generation must match the scheme the request will actually arrive on.
$visitTrackedOrders = function () use ($storefrontUrl): mixed {
    $customerId = Tenant::query()
        ->findOrFail(ProvisionTestTenantCommand::TENANT_ID)
        ->run(fn () => Customer::query()->where('email', BrowserTestFixtureSeeder::RFM_CUSTOMER_EMAIL)->value('id'));

    URL::forceRootUrl(str_replace('http://', 'https://', $storefrontUrl));
    URL::forceScheme('https');

    try {
        $signedUrl = URL::temporarySignedRoute('order.track.access', now()->addMinutes(30), ['customer' => $customerId]);
    } finally {
        URL::forceRootUrl(null);
    }

    return visit($signedUrl);
};

test('the tracking page loads the messages for each order', function () use ($visitTrackedOrders) {
    $visitTrackedOrders()
        ->assertSee('No messages yet. Say hello!')
        ->assertDontSee('Could not load messages.')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');

test('a message sent from the tracking page appears in the thread', function () use ($visitTrackedOrders) {
    $message = 'Browser test message '.bin2hex(random_bytes(4));

    $visitTrackedOrders()
        ->fill('#msg-input-BROWSER-TEST-RFM-1', $message)
        ->click('#msg-input-BROWSER-TEST-RFM-1 ~ button')
        ->assertSeeIn('#messages-BROWSER-TEST-RFM-1', $message)
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
