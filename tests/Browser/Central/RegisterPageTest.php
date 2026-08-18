<?php

$centralUrl = env('BROWSER_TEST_CENTRAL_URL', 'http://kneadit.test');

test('central register page renders without JS errors', function () use ($centralUrl) {
    visit("{$centralUrl}/register")
        ->assertVisible('input[name="email"]')
        ->assertVisible('input[name="password"]')
        ->assertSee('Create Account')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');

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

test('register submit with mismatched password confirmation is rejected by server-side validation', function () use ($centralUrl) {
    visit("{$centralUrl}/register")
        ->fill('input[name="name"]', 'Mismatch User')
        ->fill('input[name="email"]', 'mismatch-' . uniqid() . '@example.com')
        ->fill('input[name="bakery_name"]', 'Mismatch Bakery')
        ->fill('input[name="password"]', 'SecurePass123!')
        ->fill('input[name="password_confirmation"]', 'DifferentPass456!')
        ->click('input[name="terms"]')
        ->press('Create Account')
        ->assertPathIs('/register')
        ->assertVisible('input[name="email"]')
        ->assertNoJavaScriptErrors();
})->group('launch-smoke');
