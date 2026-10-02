<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

// The order tracking page loads and sends order messages over the
// {data: ...} API envelope. The fixture RFM customer has four orders, and
// opening the signed tracking link grants this browser session access to
// them (the email click-through itself is covered by the feature tests).
//
// The link is signed for the storefront host the servers use. Herd (*.test
// domains) 301-redirects http to https, which would invalidate the signature,
// so the scheme must match what the request will actually arrive on; the
// plain-http CI servers do not redirect.
//
// The signature is computed here rather than with URL::temporarySignedRoute():
// Laravel percent-encodes the brackets of an IPv6 host (the CI storefront is
// http://[::1]:PORT) when generating, but validates against the raw request
// URL, so a generated link for that host never verifies.
$visitTrackedOrders = function () use ($storefrontUrl): mixed {
    // The test process has its own in-memory database, so the customer id
    // comes from the fixture-ids file written by prepare-admin-session.mjs.
    $customerId = fixtureId('rfm_customer_id');

    $rootUrl = str_contains($storefrontUrl, '.test')
        ? str_replace('http://', 'https://', $storefrontUrl)
        : $storefrontUrl;
    $expires = now()->addMinutes(30)->getTimestamp();
    $unsigned = "{$rootUrl}/track/access/{$customerId}?expires={$expires}";
    $signature = hash_hmac('sha256', $unsigned, config()->string('app.key'));

    return visit("{$unsigned}&signature={$signature}");
};

test('the tracking page loads the messages for each order', function () use ($visitTrackedOrders) {
    $visitTrackedOrders()
        ->assertSee('No messages yet. Say hello!')
        ->assertDontSee('Could not load messages.')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');

test('a message sent from the tracking page appears in the thread', function () use ($visitTrackedOrders) {
    $message = 'Browser test message '.bin2hex(random_bytes(4));

    // Wait for the order's initial message fetch to land first; otherwise a
    // slow response could arrive after the send and overwrite the thread.
    $page = $visitTrackedOrders();

    waitForOrderMessagesLoaded($page, 'BROWSER-TEST-RFM-1')
        ->fill('#msg-input-BROWSER-TEST-RFM-1', $message)
        ->click('#msg-input-BROWSER-TEST-RFM-1 ~ button')
        ->assertSeeIn('#messages-BROWSER-TEST-RFM-1', $message)
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
