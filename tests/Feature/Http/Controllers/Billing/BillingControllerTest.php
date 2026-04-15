<?php

use App\Models\Staff\User;

beforeEach(function () {
    setUpCentralTest();
    config(['kneadit.stripe_prices' => [
        'starter' => 'price_starter_test',
        'growth' => 'price_growth_test',
        'pro' => 'price_pro_test',
    ]]);
});

test('checkout rejects invalid plan', function () {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->post(route('billing.checkout', 'nonexistent'))
        ->assertNotFound();
});

test('swap rejects invalid plan', function () {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->post(route('billing.swap', 'nonexistent'))
        ->assertNotFound();
});

test('checkout rejects plan without configured price', function () {
    config(['kneadit.stripe_prices.starter' => null]);

    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->post(route('billing.checkout', 'starter'))
        ->assertNotFound();
});
