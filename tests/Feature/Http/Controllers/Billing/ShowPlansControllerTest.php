<?php

use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Date;
use Laravel\Cashier\Subscription;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

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
        ->assertSee('Subscribe')
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

test('billing plans page names the bakery and links back to its admin', function () {
    config(['tenancy.tenant_domain' => 'kneadit.test']);

    $user = User::factory()->owner()->create();
    Tenant::factory()->create(['id' => 'sunrise', 'store_name' => 'Sunrise Bakery', 'user_id' => $user->id]);

    actingAs($user)
        ->get(route('billing.plans'))
        ->assertOk()
        ->assertSee('Sunrise Bakery')->assertSee('Back to your bakery')->assertSeeHtml('http://sunrise.kneadit.test/admin');
});

test('billing plans page tells a free-forever bakery its plan is complimentary and offers no checkout', function () {
    $user = User::factory()->owner()->create();
    Tenant::factory()->create(['free_forever' => true, 'user_id' => $user->id]);

    actingAs($user)
        ->get(route('billing.plans'))
        ->assertOk()
        ->assertSee('complimentary')
        ->assertDontSee(route('billing.checkout', 'growth'))
        ->assertDontSee(route('billing.swap', 'starter'))
        ->assertDontSee('Subscribe');
});

test('billing pages answer 404 on a bakery host instead of failing', function (bool $signedIn) {
    $user = User::factory()->owner()->create();
    $tenant = Tenant::factory()->create(['id' => 'sunrise', 'user_id' => $user->id]);
    $tenant->createDomain(['domain' => 'sunrise']);

    if ($signedIn) {
        actingAs($user);
    }

    get('http://sunrise.kneadit.test/billing/plans')->assertNotFound();
})->with([
    'signed in' => [true],
    'signed out' => [false],
]);

test('billing plans page shows when the bakery trial ends and no longer claims no card is needed', function () {
    Date::setTestNow('2026-10-05 09:00');

    $user = User::factory()->owner()->create();
    Tenant::factory()->create(['user_id' => $user->id, 'trial_ends_at' => now()->addDays(10)]);

    actingAs($user)
        ->get(route('billing.plans'))
        ->assertOk()
        ->assertSee('Your free trial ends on October 15, 2026')
        ->assertDontSee('No credit card required');
});

test('billing plans page does not show a trial end date once the trial has ended', function () {
    Date::setTestNow('2026-10-05 09:00');

    $user = User::factory()->owner()->create();
    Tenant::factory()->create(['user_id' => $user->id, 'trial_ends_at' => now()->subDay()]);

    actingAs($user)
        ->get(route('billing.plans'))
        ->assertOk()
        ->assertDontSee('Your free trial ends on')
        ->assertDontSee('No credit card required');
});
