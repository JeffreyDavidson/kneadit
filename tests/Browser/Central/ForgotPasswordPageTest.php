<?php

use Illuminate\Support\Facades\Config;

$centralUrl = Config::string('browser-testing.central_url');

test('central forgot password page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}/forgot-password")
        ->assertVisible('input[name="email"]')
        ->assertSee('Send Reset Link')
        ->assertNoJavaScriptErrors();
});
