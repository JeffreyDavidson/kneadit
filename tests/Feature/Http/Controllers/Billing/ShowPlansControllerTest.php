<?php

use App\Models\Staff\User;
use Laravel\Cashier\Subscription;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('billing plans page renders for authenticated user', function () {
    $user = User::factory()->owner()->create();

    actingAs($user)
        ->get(route('billing.plans'))
        ->assertOk();
});

test('billing plans page offers checkout, not a current plan, once the subscription has ended', function () {
    config(['kneadit.stripe_prices.growth' => 'price_growth_test']);

    $user = User::factory()->owner()->create();
    Subscription::factory()
        ->for($user, 'owner')
        ->withPrice('price_growth_test')
        ->state(['stripe_status' => 'canceled', 'ends_at' => now()->subDay()])
        ->create();

    actingAs($user)
        ->get(route('billing.plans'))
        ->assertOk()
        ->assertSee(route('billing.checkout', 'growth'))
        ->assertSee('Start Free Trial')
        ->assertDontSee('Current Plan')
        ->assertDontSee(route('billing.swap', 'starter'));
});

test('billing plans page marks the current plan while the subscription is valid', function () {
    config(['kneadit.stripe_prices.growth' => 'price_growth_test']);

    $user = User::factory()->owner()->create();
    Subscription::factory()
        ->for($user, 'owner')
        ->withPrice('price_growth_test')
        ->create();

    actingAs($user)
        ->get(route('billing.plans'))
        ->assertOk()
        ->assertSee('Current Plan')
        ->assertSee(route('billing.swap', 'starter'));
});
