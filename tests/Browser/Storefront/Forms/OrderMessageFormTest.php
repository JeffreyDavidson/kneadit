<?php

use Database\Seeders\BrowserTestFixtureSeeder;

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

// The order tracking page loads and sends order messages over the
// {data: ...} API envelope. The fixture RFM customer has four orders, so
// looking them up by email also grants this browser session access to them.

function trackFixtureOrders(mixed $page): mixed
{
    return $page
        ->fill('#email', BrowserTestFixtureSeeder::RFM_CUSTOMER_EMAIL)
        ->click('button[type="submit"]');
}

test('the tracking page loads the messages for each order', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/track");

    trackFixtureOrders($page)
        ->assertSee('No messages yet. Say hello!')
        ->assertDontSee('Could not load messages.')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');

test('a message sent from the tracking page appears in the thread', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/track");
    $message = 'Browser test message '.bin2hex(random_bytes(4));

    trackFixtureOrders($page)
        ->fill('#msg-input-BROWSER-TEST-RFM-1', $message)
        ->click('#msg-input-BROWSER-TEST-RFM-1 ~ button')
        ->assertSeeIn('#messages-BROWSER-TEST-RFM-1', $message)
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
