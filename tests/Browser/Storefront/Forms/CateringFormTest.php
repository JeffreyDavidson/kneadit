<?php

use Illuminate\Support\Facades\Config;

$storefrontUrl = Config::string('browser-testing.storefront_url');

test('catering inquiry form blocks empty submit via HTML5 required', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/catering")
        ->press('Submit Inquiry')
        ->assertPathIs('/catering')
        ->assertVisible('[data-test="catering-form"]')
        ->assertNoJavaScriptErrors();
});

test('catering inquiry form submits successfully and shows thank-you flash', function () use ($storefrontUrl) {
    // Date 60 days out to safely exceed any tenant lead-time setting
    $eventDate = date('Y-m-d', strtotime('+60 days'));

    visit("{$storefrontUrl}/catering")
        ->fill('[data-test="catering-form-customer-name"]', 'Test Inquirer')
        ->fill('[data-test="catering-form-customer-email"]', 'test-inquirer@example.com')
        ->select('[data-test="catering-form-event-type"]', 'Wedding')
        ->fill('[data-test="catering-form-event-date"]', $eventDate)
        ->fill('[data-test="catering-form-guest-count"]', '50')
        ->fill('[data-test="catering-form-details"]', 'Looking for cupcakes for 50 guests at a wedding reception.')
        ->press('Submit Inquiry')
        ->assertSee('Thank you for your inquiry')
        ->assertNoJavaScriptErrors();
});
