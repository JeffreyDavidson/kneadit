<?php

use App\Filament\Central\Resources\TenantResource\Pages\ListTenants;
use App\Models\Platform\FreeForeverGrant;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Attributes\Table;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Subscription;

use function Pest\Livewire\livewire;

/**
 * Stands in for Cashier's subscription so cancelling never calls Stripe.
 */
#[Table(name: 'subscriptions')]
class GrantRecordingSubscription extends Subscription
{
    #[Override]
    public function getForeignKey(): string
    {
        return 'subscription_id';
    }

    #[Override]
    public function cancel()
    {
        $this->forceFill(['ends_at' => now()->addMonth()])->save();

        return $this;
    }

    #[Override]
    public function cancelNow()
    {
        $this->forceFill(['stripe_status' => 'canceled', 'ends_at' => now()])->save();

        return $this;
    }
}

beforeEach(function () {
    setUpCentralTest();
    Cashier::useSubscriptionModel(GrantRecordingSubscription::class);
    test()->admin = User::factory()->platformAdmin()->create();
    test()->actingAs(test()->admin);
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

afterEach(fn () => Cashier::useSubscriptionModel(Subscription::class));

function subscribedBakery(string $id, string $status): Tenant
{
    $owner = User::factory()->owner()->create();
    $owner->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => "sub_{$id}",
        'stripe_status' => $status,
        'stripe_price' => 'price_starter_test',
    ]);

    return Tenant::factory()->create(['id' => $id, 'user_id' => $owner->id, 'plan' => 'starter']);
}

test('granting free forever cancels the owner\'s paid subscription at the end of the period', function () {
    $tenant = subscribedBakery('paying', 'active');

    livewire(ListTenants::class)
        ->selectTableRecords([$tenant->id])
        ->callAction(TestAction::make('grant_free_forever')->table()->bulk());

    $subscription = $tenant->owner->subscription('default');

    expect($tenant->refresh()->free_forever)->toBeTrue()
        ->and($subscription->onGracePeriod())->toBeTrue()
        ->and(FreeForeverGrant::query()->where('tenant_id', $tenant->id)->count())->toBe(1);
});

test('granting free forever to a past-due owner ends the subscription now so it cannot charge again', function () {
    $tenant = subscribedBakery('overdue', 'past_due');

    livewire(ListTenants::class)
        ->selectTableRecords([$tenant->id])
        ->callAction(TestAction::make('grant_free_forever')->table()->bulk());

    expect($tenant->refresh()->free_forever)->toBeTrue()
        ->and($tenant->owner->subscription('default')->ended())->toBeTrue();
});

test('granting free forever to an owner without a subscription just grants it', function () {
    $tenant = Tenant::factory()->create(['id' => 'no-sub', 'user_id' => User::factory()->owner()->create()->id]);

    livewire(ListTenants::class)
        ->selectTableRecords([$tenant->id])
        ->callAction(TestAction::make('grant_free_forever')->table()->bulk());

    expect($tenant->refresh()->free_forever)->toBeTrue();
});
