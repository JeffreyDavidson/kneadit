<?php

use App\Enums\Platform\SubscriptionTier;
use App\Http\Controllers\Stripe\StripeWebhookController;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;

beforeEach(function () {
    setUpCentralTest();
    config([
        'tenancy.central_domains' => ['localhost', 'kneadit.test'],
        'kneadit.stripe_prices' => ['growth' => 'price_growth_paused'],
    ]);
});

/**
 * Runs one of Cashier's webhook handlers with a subscription payload for the owner.
 *
 * @param  'handleCustomerSubscriptionCreated'|'handleCustomerSubscriptionUpdated'|'handleCustomerSubscriptionDeleted'  $handler
 */
function sendSubscriptionEvent(string $handler, User $owner, string $status, string $subscriptionId = 'sub_paused_state'): void
{
    $controller = app(StripeWebhookController::class);

    new ReflectionMethod($controller, $handler)->invoke($controller, [
        'id' => 'evt_paused_state_'.uniqid(),
        'data' => [
            'object' => [
                'id' => $subscriptionId,
                'customer' => $owner->stripe_id,
                'status' => $status,
                'metadata' => ['type' => 'default'],
                'items' => [
                    'data' => [[
                        'id' => "si_{$subscriptionId}",
                        'quantity' => 1,
                        'price' => ['id' => 'price_growth_paused', 'product' => 'prod_paused_state'],
                    ]],
                ],
            ],
        ],
    ]);
}

function pausedBakeryOf(User $owner, SubscriptionTier $plan = SubscriptionTier::Growth): Tenant
{
    return Tenant::factory()->for($owner, 'owner')->create([
        'email' => $owner->email,
        'plan' => $plan,
        'paused_at' => now()->subDay(),
    ]);
}

test('a subscription that starts or turns valid resumes the paused bakery', function (string $handler, string $status) {
    $owner = User::factory()->owner()->create(['stripe_id' => 'cus_resume_'.uniqid()]);
    $tenant = pausedBakeryOf($owner, SubscriptionTier::Starter);

    sendSubscriptionEvent($handler, $owner, $status);

    expect($tenant->refresh()->paused_at)->toBeNull()
        ->and($tenant->plan)->toBe(SubscriptionTier::Growth);
})->with([
    'created active' => ['handleCustomerSubscriptionCreated', 'active'],
    'created trialing' => ['handleCustomerSubscriptionCreated', 'trialing'],
    'updated active' => ['handleCustomerSubscriptionUpdated', 'active'],
    'updated trialing' => ['handleCustomerSubscriptionUpdated', 'trialing'],
]);

test('a subscription that is not valid leaves the bakery paused', function (string $status) {
    $owner = User::factory()->owner()->create(['stripe_id' => 'cus_not_valid_'.uniqid()]);
    $tenant = pausedBakeryOf($owner);

    sendSubscriptionEvent('handleCustomerSubscriptionUpdated', $owner, $status);

    expect($tenant->refresh()->paused_at)->not->toBeNull();
})->with(['canceled', 'incomplete_expired', 'unpaid', 'past_due', 'incomplete']);

test('an ended subscription resets the bakery plan without pausing it', function (string $handler, string $status) {
    $owner = User::factory()->owner()->create(['stripe_id' => 'cus_ended_'.uniqid()]);
    $tenant = Tenant::factory()->for($owner, 'owner')->create(['email' => $owner->email, 'plan' => SubscriptionTier::Pro]);

    sendSubscriptionEvent($handler, $owner, $status);

    expect($tenant->refresh()->plan)->toBe(SubscriptionTier::Starter)
        ->and($tenant->paused_at)->toBeNull();
})->with([
    'deleted' => ['handleCustomerSubscriptionDeleted', 'canceled'],
    'updated to canceled' => ['handleCustomerSubscriptionUpdated', 'canceled'],
    'updated to incomplete_expired' => ['handleCustomerSubscriptionUpdated', 'incomplete_expired'],
    'updated to unpaid' => ['handleCustomerSubscriptionUpdated', 'unpaid'],
]);

test('a payment problem keeps the plan while the subscription is retried', function (string $status) {
    $owner = User::factory()->owner()->create(['stripe_id' => 'cus_dunning_'.uniqid()]);
    $tenant = Tenant::factory()->for($owner, 'owner')->create(['email' => $owner->email, 'plan' => SubscriptionTier::Growth]);

    sendSubscriptionEvent('handleCustomerSubscriptionUpdated', $owner, $status);

    expect($tenant->refresh()->plan)->toBe(SubscriptionTier::Growth);
})->with(['past_due', 'incomplete']);

test('an old subscription ending does not reset the plan of an owner with a valid one', function () {
    $owner = User::factory()->owner()->create(['stripe_id' => 'cus_two_subs_'.uniqid()]);
    $tenant = Tenant::factory()->for($owner, 'owner')->create(['email' => $owner->email, 'plan' => SubscriptionTier::Growth]);
    sendSubscriptionEvent('handleCustomerSubscriptionCreated', $owner, 'active', 'sub_new');

    sendSubscriptionEvent('handleCustomerSubscriptionDeleted', $owner, 'canceled', 'sub_old');

    expect($tenant->refresh()->plan)->toBe(SubscriptionTier::Growth);
});
