<?php

use App\Http\Controllers\Billing\CheckoutController;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;
use Laravel\Cashier\Checkout;
use Laravel\Cashier\Subscription;
use Laravel\Cashier\SubscriptionBuilder;

/**
 * Stands in for the signed-in user so the controller's newSubscription() call
 * returns a doubled builder and never reaches Stripe.
 */
#[Table(name: 'users')]
final class CheckoutBuilderUser extends User
{
    public ?SubscriptionBuilder $fakeBuilder = null;

    /** @var list<string> */
    public array $requestedPrices = [];

    #[Override]
    public function getForeignKey(): string
    {
        return 'user_id';
    }

    #[Override]
    public function newSubscription(string $type, string|array $prices = []): SubscriptionBuilder
    {
        $this->requestedPrices = (array) $prices;

        return $this->fakeBuilder ?? parent::newSubscription($type, $prices);
    }
}

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

test('checkout refuses to start a second subscription', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $user = User::factory()->owner()->create();
    Subscription::factory()
        ->for($user, 'owner')
        ->withPrice('price_starter_test')
        ->create();

    $this->actingAs($user)
        ->post(route('billing.checkout', 'starter'))
        ->assertRedirect(route('billing.plans'))
        ->assertSessionHas('error', 'You already have a subscription. Use Switch to change plans.');

    expect($user->subscriptions()->count())->toBe(1);
});

test('checkout refuses a free-forever bakery owner without calling Stripe', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $user = User::factory()->owner()->create();
    Tenant::factory()->create(['free_forever' => true, 'user_id' => $user->id]);

    $builder = Double::for(SubscriptionBuilder::class);
    $builder->expects('trialDays')->never();
    $builder->expects('checkout')->never();

    $subject = CheckoutBuilderUser::query()->findOrFail($user->id);
    $subject->fakeBuilder = $builder;

    $response = app(CheckoutController::class)($subject, 'starter');

    expect($subject->requestedPrices)->toBeEmpty()
        ->and($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe(route('billing.plans'))
        ->and(session('error'))->toBe('Your bakery has a complimentary plan, so there is nothing to subscribe to.');
});

test('checkout ends the Stripe trial when the bakery trial ends', function () {
    Date::setTestNow('2026-10-05 09:00');
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $user = User::factory()->owner()->create();
    Tenant::factory()->create(['user_id' => $user->id, 'trial_ends_at' => now()->addDays(10)]);

    $builder = Double::for(SubscriptionBuilder::class);
    $builder->expects('trialUntil')
        ->with(Argument::satisfies(fn (mixed $date): bool => $date instanceof CarbonInterface
            && $date->toDateString() === '2026-10-15'))
        ->returns($builder);
    $builder->expects('trialDays')->never();
    $builder->expects('allowPromotionCodes')->returns($builder);
    $builder->expects('checkout')->returns(Double::for(Checkout::class));

    $subject = CheckoutBuilderUser::query()->findOrFail($user->id);
    $subject->fakeBuilder = $builder;

    app(CheckoutController::class)($subject, 'starter');

    expect($subject->requestedPrices)->toBe(['price_starter_test']);
});

test('checkout gives no Stripe trial once the bakery trial has ended', function () {
    Date::setTestNow('2026-10-05 09:00');
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $user = User::factory()->owner()->create();
    Tenant::factory()->create(['user_id' => $user->id, 'trial_ends_at' => now()->subDay()]);

    $builder = Double::for(SubscriptionBuilder::class);
    $builder->expects('trialUntil')->never();
    $builder->expects('trialDays')->never();
    $builder->expects('allowPromotionCodes')->returns($builder);
    $builder->expects('checkout')->returns(Double::for(Checkout::class));

    $subject = CheckoutBuilderUser::query()->findOrFail($user->id);
    $subject->fakeBuilder = $builder;

    app(CheckoutController::class)($subject, 'starter');

    expect($subject->requestedPrices)->toBe(['price_starter_test']);
});

test('checkout gives no Stripe trial to an owner without a bakery', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $user = User::factory()->owner()->create();

    $builder = Double::for(SubscriptionBuilder::class);
    $builder->expects('trialUntil')->never();
    $builder->expects('trialDays')->never();
    $builder->expects('allowPromotionCodes')->returns($builder);
    $builder->expects('checkout')->returns(Double::for(Checkout::class));

    $subject = CheckoutBuilderUser::query()->findOrFail($user->id);
    $subject->fakeBuilder = $builder;

    app(CheckoutController::class)($subject, 'starter');

    expect($subject->requestedPrices)->toBe(['price_starter_test']);
});

test('checkout gives no Stripe trial to a returning subscriber whose bakery trial ended', function () {
    Date::setTestNow('2026-10-05 09:00');
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test']]);

    $user = User::factory()->owner()->create();
    Tenant::factory()->create(['user_id' => $user->id, 'trial_ends_at' => now()->subMonth()]);
    Subscription::factory()
        ->for($user, 'owner')
        ->withPrice('price_starter_test')
        ->state(['stripe_status' => 'canceled', 'ends_at' => now()->subMonth()])
        ->create();

    $builder = Double::for(SubscriptionBuilder::class);
    $builder->expects('trialUntil')->never();
    $builder->expects('trialDays')->never();
    $builder->expects('allowPromotionCodes')->returns($builder);
    $builder->expects('checkout')->returns(Double::for(Checkout::class));

    $subject = CheckoutBuilderUser::query()->findOrFail($user->id);
    $subject->fakeBuilder = $builder;

    app(CheckoutController::class)($subject, 'starter');

    expect($subject->requestedPrices)->toBe(['price_starter_test']);
});
