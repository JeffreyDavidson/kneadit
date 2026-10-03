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

function ownerWithSubscription(string $email = 'owner@example.com', string $status = 'active'): User
{
    $owner = User::factory()->owner()->create(['email' => $email]);

    $owner->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_test_123',
        'stripe_status' => $status,
        'stripe_price' => 'price_starter_test',
    ]);

    return $owner;
}

test('deleting a bakery cancels its owner\'s subscription at the end of the period', function (): void {
    $owner = ownerWithSubscription();
    createTenantWithDomain('sweet-treats', attributes: ['email' => $owner->email]);

    Tenant::query()->findOrFail('sweet-treats')->delete();

    $subscription = $owner->subscription('default');
    expect($subscription->ends_at)->not->toBeNull()
        ->and($subscription->onGracePeriod())->toBeTrue();
});

test('deleting a bakery keeps the subscription when the owner has another bakery', function (): void {
    $owner = ownerWithSubscription();
    createTenantWithDomain('sweet-treats', attributes: ['email' => $owner->email]);
    createTenantWithDomain('second-shop', attributes: ['email' => $owner->email]);

    Tenant::query()->findOrFail('sweet-treats')->delete();

    expect($owner->subscription('default')->ends_at)->toBeNull();
});

test('deleting a bakery leaves an already canceled subscription alone', function (): void {
    $owner = ownerWithSubscription(status: 'canceled');
    $owner->subscription('default')->forceFill(['ends_at' => now()->subDay()])->save();
    createTenantWithDomain('sweet-treats', attributes: ['email' => $owner->email]);
    CancelRecordingSubscription::$failCancel = true;

    Tenant::query()->findOrFail('sweet-treats')->delete();

    expect(Tenant::query()->find('sweet-treats'))->toBeNull();
});

test('deleting a bakery whose owner has no subscription still deletes it', function (): void {
    $owner = User::factory()->owner()->create(['email' => 'owner@example.com']);
    createTenantWithDomain('sweet-treats', attributes: ['email' => $owner->email]);

    Tenant::query()->findOrFail('sweet-treats')->delete();

    expect(Tenant::query()->find('sweet-treats'))->toBeNull();
});

test('the bakery is not deleted when Stripe refuses the cancel', function (): void {
    $owner = ownerWithSubscription();
    createTenantWithDomain('sweet-treats', attributes: ['email' => $owner->email]);
    CancelRecordingSubscription::$failCancel = true;

    expect(fn () => Tenant::query()->findOrFail('sweet-treats')->delete())
        ->toThrow(RuntimeException::class, 'Stripe is unavailable.');

    expect(Tenant::query()->find('sweet-treats'))->not->toBeNull();
});
