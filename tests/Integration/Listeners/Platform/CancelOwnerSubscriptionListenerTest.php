<?php

use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Support\Facades\Event;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Subscription;
use Stancl\Tenancy\Events\TenantDeleted;

/**
 * Stands in for Cashier's subscription so cancel() never calls Stripe.
 */
#[Table(name: 'subscriptions')]
class CancelRecordingSubscription extends Subscription
{
    public static bool $failCancel = false;

    #[Override]
    public function getForeignKey(): string
    {
        return 'subscription_id';
    }

    #[Override]
    public function cancelNow()
    {
        throw_if(self::$failCancel, RuntimeException::class, 'Stripe is unavailable.');

        $this->forceFill(['stripe_status' => 'canceled', 'ends_at' => now()])->save();

        return $this;
    }

    #[Override]
    public function cancel()
    {
        throw_if(self::$failCancel, RuntimeException::class, 'Stripe is unavailable.');

        $this->forceFill(['ends_at' => now()->addMonth()])->save();

        return $this;
    }
}

beforeEach(function (): void {
    setUpCentralTest();
    Event::fake([TenantDeleted::class]);
    Cashier::useSubscriptionModel(CancelRecordingSubscription::class);
    CancelRecordingSubscription::$failCancel = false;
});

afterEach(fn () => Cashier::useSubscriptionModel(Subscription::class));

function ownerWithSubscription(string $email = 'owner@example.com', string $status = 'active', string $stripeId = 'sub_test_123'): User
{
    $owner = User::query()->firstWhere('email', $email) ?? User::factory()->owner()->create(['email' => $email]);

    $owner->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => $stripeId,
        'stripe_status' => $status,
        'stripe_price' => 'price_starter_test',
    ]);

    return $owner;
}

test('deleting a bakery cancels its owner\'s subscription at the end of the period', function (): void {
    $owner = ownerWithSubscription();
    createTenantWithDomain('sweet-treats', attributes: ['email' => $owner->email, 'user_id' => $owner->id]);

    Tenant::query()->findOrFail('sweet-treats')->delete();

    $subscription = $owner->subscription('default');
    expect($subscription->ends_at)->not->toBeNull()
        ->and($subscription->onGracePeriod())->toBeTrue();
});

test('deleting a bakery cancels its owner\'s subscription after the owner changed their email', function (): void {
    $owner = ownerWithSubscription('new-address@example.com');
    createTenantWithDomain('sweet-treats', attributes: ['email' => 'old-address@example.com', 'user_id' => $owner->id]);

    Tenant::query()->findOrFail('sweet-treats')->delete();

    expect($owner->subscription('default')->ends_at)->not->toBeNull();
});

test('deleting a bakery with no linked owner cancels nothing, even if a user shares its email', function (): void {
    $user = ownerWithSubscription();
    createTenantWithDomain('sweet-treats', attributes: ['email' => $user->email, 'user_id' => null]);

    Tenant::query()->findOrFail('sweet-treats')->delete();

    expect(Tenant::query()->find('sweet-treats'))->toBeNull()
        ->and($user->subscription('default')->ends_at)->toBeNull();
});

test('deleting a bakery leaves an already canceled subscription alone', function (): void {
    $owner = ownerWithSubscription(status: 'canceled');
    $owner->subscription('default')->forceFill(['ends_at' => now()->subDay()])->save();
    createTenantWithDomain('sweet-treats', attributes: ['email' => $owner->email, 'user_id' => $owner->id]);
    CancelRecordingSubscription::$failCancel = true;

    Tenant::query()->findOrFail('sweet-treats')->delete();

    expect(Tenant::query()->find('sweet-treats'))->toBeNull();
});

test('deleting a bakery whose owner has no subscription still deletes it', function (): void {
    $owner = User::factory()->owner()->create(['email' => 'owner@example.com']);
    createTenantWithDomain('sweet-treats', attributes: ['email' => $owner->email, 'user_id' => $owner->id]);

    Tenant::query()->findOrFail('sweet-treats')->delete();

    expect(Tenant::query()->find('sweet-treats'))->toBeNull();
});

test('the bakery is not deleted when Stripe refuses the cancel', function (): void {
    $owner = ownerWithSubscription();
    createTenantWithDomain('sweet-treats', attributes: ['email' => $owner->email, 'user_id' => $owner->id]);
    CancelRecordingSubscription::$failCancel = true;

    expect(fn () => Tenant::query()->findOrFail('sweet-treats')->delete())
        ->toThrow(RuntimeException::class, 'Stripe is unavailable.')
        ->and(Tenant::query()->find('sweet-treats'))->not->toBeNull();
});

test('deleting a bakery stops billing on a past-due subscription right away', function (string $status): void {
    $owner = ownerWithSubscription(status: $status);
    createTenantWithDomain('sweet-treats', attributes: ['email' => $owner->email, 'user_id' => $owner->id]);

    Tenant::query()->findOrFail('sweet-treats')->delete();

    $subscription = $owner->subscription('default');
    expect(Tenant::query()->find('sweet-treats'))->toBeNull()
        ->and($subscription->stripe_status)->toBe('canceled')
        ->and($subscription->ended())->toBeTrue();
})->with(['past_due', 'incomplete', 'unpaid']);

test('deleting a bakery cancels every subscription that has not ended, not only the newest', function (): void {
    $owner = ownerWithSubscription(status: 'past_due', stripeId: 'sub_older');
    ownerWithSubscription(status: 'active', stripeId: 'sub_newer');
    createTenantWithDomain('sweet-treats', attributes: ['email' => $owner->email, 'user_id' => $owner->id]);

    Tenant::query()->findOrFail('sweet-treats')->delete();

    $subscriptions = $owner->subscriptions()->orderBy('id')->get();
    expect($subscriptions)->toHaveCount(2)
        ->and($subscriptions[0]->ended())->toBeTrue()
        ->and($subscriptions[1]->onGracePeriod())->toBeTrue();
});
