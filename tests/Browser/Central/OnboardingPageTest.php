<?php

$centralUrl = Illuminate\Support\Facades\Config::string('browser-testing.central_url');

test('central onboarding page renders for an authenticated user without JS errors', function () use ($centralUrl) {
    authenticatedCentralVisit("{$centralUrl}/onboarding")
        ->assertVisible('input[name="store_name"]')
        ->assertVisible('input[name="subdomain"]')
        ->assertSee('Use KneadIt')
        ->assertSee('I Have My Own')
        ->assertSee('Create My Bakery')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');

test('empty onboarding submit is blocked by HTML5 required and stays on /onboarding', function () use ($centralUrl) {
    authenticatedCentralVisit("{$centralUrl}/onboarding")
        ->press('Create My Bakery →')
        ->assertPathIs('/onboarding')
        ->assertVisible('input[name="store_name"]')
        ->assertNoJavaScriptErrors();
});
