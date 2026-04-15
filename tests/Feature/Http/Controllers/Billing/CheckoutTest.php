<?php

use App\Models\Staff\User;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('checkout returns 404 for invalid plan', function () {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->post(route('billing.checkout', 'nonexistent-plan'))
        ->assertNotFound();
});

test('checkout returns 404 for valid tier but no configured price', function () {
    config(['kneadit.stripe_prices' => ['starter' => null]]);

    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->post(route('billing.checkout', 'starter'))
        ->assertNotFound();
});

test('checkout requires authentication', function () {
    $this->post(route('billing.checkout', 'starter'))
        ->assertRedirect(route('login'));
});
