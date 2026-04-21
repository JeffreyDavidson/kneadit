<?php

$centralUrl = env('BROWSER_TEST_CENTRAL_URL', 'http://kneadit.test');

test('central register page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}/register")
        ->assertVisible('input[name="email"]')
        ->assertVisible('input[name="password"]')
        ->assertSee('Create Account')
        ->assertNoJavaScriptErrors();
});
