<?php

use App\Models\Staff\User;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('checkout success redirects authenticated user to onboarding', function () {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->get(route('billing.success'))
        ->assertRedirect(route('onboarding.show'));
});

test('checkout success skips stripe check for authenticated user with session_id', function () {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->get(route('billing.success', ['session_id' => 'cs_should_not_matter']))
        ->assertRedirect(route('onboarding.show'));
});

test('checkout success controller checks session status and customer', function () {
    $controllerSource = file_get_contents(app_path('Http/Controllers/Billing/CheckoutSuccessController.php'));
    $actionSource = file_get_contents(app_path('Actions/Stripe/ReauthenticateFromCheckoutSession.php'));

    expect($controllerSource)
        ->toContain('$request->has(\'session_id\')')
        ->and($actionSource)
        ->toContain('Cashier::stripe()->checkout->sessions->retrieve')
        ->toContain('$checkoutSession->status')
        ->toContain('$checkoutSession->customer')
        ->toContain('subMinutes(30)')
        ->toContain('Auth::login($user)');
});

test('checkout success controller re-auths only when user is not already logged in', function () {
    $source = file_get_contents(app_path('Http/Controllers/Billing/CheckoutSuccessController.php'));

    expect($source)
        ->toContain('! $request->user() && $request->has(\'session_id\')');
});

test('checkout success controller looks up user by stripe_id', function () {
    $source = file_get_contents(app_path('Actions/Stripe/ReauthenticateFromCheckoutSession.php'));

    expect($source)
        ->toContain("User::query()->where('stripe_id', \$checkoutSession->customer)->first()");
});

test('checkout success always redirects to onboarding', function () {
    $source = file_get_contents(app_path('Http/Controllers/Billing/CheckoutSuccessController.php'));

    expect($source)->toContain("return redirect('/onboarding')");
});
