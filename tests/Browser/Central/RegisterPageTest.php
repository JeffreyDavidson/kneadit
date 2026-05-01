<?php

$centralUrl = env('BROWSER_TEST_CENTRAL_URL', 'http://kneadit.test');

test('central register page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}/register")
        ->assertVisible('input[name="email"]')
        ->assertVisible('input[name="password"]')
        ->assertSee('Create Account')
        ->assertNoJavaScriptErrors();
});

test('central register page exposes every required input', function () use ($centralUrl) {
    visit("{$centralUrl}/register")
        ->assertVisible('input[name="name"]')
        ->assertVisible('input[name="email"]')
        ->assertVisible('input[name="bakery_name"]')
        ->assertVisible('input[name="password"]')
        ->assertVisible('input[name="password_confirmation"]')
        ->assertVisible('input[name="terms"]')
        ->assertNoJavaScriptErrors();
});

test('empty register submit is blocked by HTML5 required and stays on /register', function () use ($centralUrl) {
    visit("{$centralUrl}/register")
        ->press('Create Account')
        ->assertPathIs('/register')
        ->assertVisible('input[name="email"]')
        ->assertNoJavaScriptErrors();
});
