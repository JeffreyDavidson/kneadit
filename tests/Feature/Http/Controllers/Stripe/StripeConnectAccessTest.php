<?php

declare(strict_types=1);

use App\Actions\Stripe\InitiateStripeConnect;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Double;

use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('guests are sent to login', function () {
    withoutMiddleware(tenantMiddleware())
        ->get(route('stripe.connect'))
        ->assertRedirect(route('login'));
});

test('only the owner can start Stripe Connect onboarding', function (string $state) {
    $initiateConnect = Double::for(InitiateStripeConnect::class);
    $initiateConnect->expects('__invoke')->never();
    app()->instance(InitiateStripeConnect::class, $initiateConnect);

    withoutMiddleware(tenantMiddleware())
        ->actingAs(User::factory()->{$state}()->create())
        ->get(route('stripe.connect'))
        ->assertForbidden();
})->with([
    'manager' => 'manager',
    'staff' => 'staff',
]);

test('the owner is redirected to Stripe', function () {
    $initiateConnect = Double::for(InitiateStripeConnect::class);
    $initiateConnect->expects('__invoke')->returns('https://connect.stripe.com/setup/test123');
    app()->instance(InitiateStripeConnect::class, $initiateConnect);

    withoutMiddleware(tenantMiddleware())
        ->actingAs(User::factory()->owner()->create())
        ->get(route('stripe.connect'))
        ->assertRedirect('https://connect.stripe.com/setup/test123');
});
