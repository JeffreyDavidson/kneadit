<?php

use App\Filament\Central\Resources\TenantResource\Pages\ListTenants;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Notifications\Livewire\Notifications;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantDeleted;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    Event::fake([TenantDeleted::class]);
    test()->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

function bakeryOwnerWithSubscription(string $status = 'active'): User
{
    $owner = User::factory()->owner()->create();

    $owner->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_test_123',
        'stripe_status' => $status,
        'stripe_price' => 'price_starter_test',
    ]);

    return $owner;
}

test('a platform admin can bulk delete bakeries without subscriptions', function () {
    createTenantWithDomain('sweet-bakes');
    createTenantWithDomain('rustic-loaf');

    livewire(ListTenants::class)
        ->selectTableRecords(['sweet-bakes', 'rustic-loaf'])
        ->callAction(TestAction::make('delete')->table()->bulk())
        ->assertNotified();

    expect(Tenant::query()->whereIn('id', ['sweet-bakes', 'rustic-loaf'])->count())->toBe(0);
});

test('a bakery whose owner has a valid subscription is not deleted but the others are', function () {
    $owner = bakeryOwnerWithSubscription();
    createTenantWithDomain('sweet-bakes', attributes: ['email' => $owner->email, 'user_id' => $owner->id]);
    createTenantWithDomain('rustic-loaf');

    livewire(ListTenants::class)
        ->selectTableRecords(['sweet-bakes', 'rustic-loaf'])
        ->callAction(TestAction::make('delete')->table()->bulk());

    $notifications = new Notifications;
    $notifications->mount();

    $notification = $notifications->notifications->first()->toArray();

    expect($notification['title'])->toBe('Deleted 1 of 2')
        ->and($notification['body'])->toContain("Cancel this bakery's subscription first.")
        ->and(Tenant::query()->find('sweet-bakes'))->not->toBeNull()
        ->and(Tenant::query()->find('rustic-loaf'))->toBeNull();
});

test('a bakery whose owner has an ended subscription can be deleted', function () {
    $owner = bakeryOwnerWithSubscription('canceled');
    $owner->subscription('default')->forceFill(['ends_at' => now()->subDay()])->save();
    createTenantWithDomain('sweet-bakes', attributes: ['email' => $owner->email, 'user_id' => $owner->id]);

    livewire(ListTenants::class)
        ->selectTableRecords(['sweet-bakes'])
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(Tenant::query()->find('sweet-bakes'))->toBeNull();
});
