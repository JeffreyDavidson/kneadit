<?php

use Illuminate\Support\Facades\Config;

$storefrontUrl = Config::string('browser-testing.storefront_url');

test('contact form submits successfully and shows success alert', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/contact")
        ->assertVisible('[data-test="contact-form"]')
        ->fill('[data-test="contact-form-name"]', 'Jane Smith')
        ->fill('[data-test="contact-form-email"]', 'jane@example.com')
        ->fill('[data-test="contact-form-subject"]', 'Custom cake question')
        ->fill('[data-test="contact-form-message"]', 'Do you make gluten-free birthday cakes?')
        ->press('Send Message')
        ->assertSee('Thank you for your message')
        ->assertNoJavaScriptErrors();
});

test('contact form shows browser-level required validation when fields are empty', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/contact")
        ->press('Send Message')
        // The form has HTML5 required attributes; the browser should block the submit
        // and the form should still be visible (no navigation away from /contact).
        ->assertPathIs('/contact')
        ->assertVisible('[data-test="contact-form"]')
        ->assertNoJavaScriptErrors();
});
