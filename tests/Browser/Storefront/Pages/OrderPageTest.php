<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

test('order page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/order")
        ->assertVisible('[data-test="page-order-create"]')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');

// Pest's browser plugin only records console.log, but Alpine reports a missing
// plugin or a second copy of itself through console.warn. So the test listens
// to console.warn itself, then initialises an x-collapse element, which makes
// Alpine warn if the Collapse plugin was never registered on the running
// instance.
test('order page runs one Alpine instance with the collapse plugin registered', function () use ($storefrontUrl) {
    $page = visit("{$storefrontUrl}/order")
        ->assertVisible('[data-test="page-order-create"]');

    $warnings = $page->script(<<<'JS'
        (() => {
            const warnings = [];
            console.warn = (...args) => warnings.push(args.join(' '));

            const host = document.createElement('div');
            host.innerHTML = '<div x-data="{ open: true }"><div x-show="open" x-collapse></div></div>';
            document.body.appendChild(host);
            window.Alpine.initTree(host);
            host.remove();

            return warnings;
        })()
    JS);

    expect($warnings)->toBe([]);
    $page->assertScript('document.querySelectorAll("script[src*=livewire]").length === 0');
})->group('launch-smoke');
