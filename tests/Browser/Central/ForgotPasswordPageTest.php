<?php

$centralUrl = env('BROWSER_TEST_CENTRAL_URL', 'http://kneadit.test');

test('central forgot password page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}/forgot-password")
        ->assertVisible('input[name="email"]')
        ->assertSee('Send Reset Link')
        ->assertNoJavaScriptErrors();
});
