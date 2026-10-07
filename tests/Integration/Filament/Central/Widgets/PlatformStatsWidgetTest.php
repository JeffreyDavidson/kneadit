<?php

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Central\Widgets\PlatformStats;
use App\Filament\Central\Widgets\RevenueOverview;
use App\Models\Platform\SupportTicket;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Laravel\Cashier\Subscription;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('the MRR stat sums valid paid subscriptions, not every active bakery', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_test', 'growth' => 'price_growth_test', 'pro' => 'price_pro_test']]);

    foreach (['bakery1' => 'price_starter_test', 'bakery2' => 'price_growth_test'] as $id => $price) {
        $owner = User::factory()->owner()->create();
        createTenant(['id' => $id, 'name' => $id, 'email' => "{$id}@test.com", 'user_id' => $owner->id]);
        Subscription::factory()->for($owner, 'owner')->withPrice($price)->create();
    }

    // Active, but on the free trial: no MRR.
    createTenant(['id' => 'bakery3', 'name' => 'B3', 'email' => 'b3@test.com', 'plan' => SubscriptionTier::Pro, 'trial_ends_at' => now()->addDays(7)]);

    livewire(PlatformStats::class)
        ->assertSee('$28.00')
        ->assertSee('2 paying');

    livewire(RevenueOverview::class)
        ->assertSee('$14.00')
        ->assertSee('$336.00');
});

test('trial count', function () {
    createTenant(['id' => 't1', 'name' => 'T1', 'email' => 't1@test.com', 'plan' => SubscriptionTier::Starter, 'trial_ends_at' => now()->addDays(7)]);
    createTenant(['id' => 't2', 'name' => 'T2', 'email' => 't2@test.com', 'plan' => SubscriptionTier::Starter, 'trial_ends_at' => now()->addDays(14)]);
    createTenant(['id' => 't3', 'name' => 'T3', 'email' => 't3@test.com', 'plan' => SubscriptionTier::Starter, 'trial_ends_at' => now()->subDays(1)]);
    createTenant(['id' => 't4', 'name' => 'T4', 'email' => 't4@test.com', 'plan' => SubscriptionTier::Growth]);

    $trialCount = Tenant::query()->whereNotNull('trial_ends_at')
        ->where('trial_ends_at', '>', now())
        ->count();

    expect($trialCount)->toBe(2);
});

test('open tickets count', function () {
    SupportTicket::factory()->open()->count(2)->create();
    SupportTicket::factory()->closed()->create();

    expect(SupportTicket::query()->where('status', 'open')->count())->toBe(2);
});

test('total tenants count', function () {
    createTenant(['id' => 'a1', 'name' => 'A1', 'email' => 'a1@test.com', 'plan' => SubscriptionTier::Starter]);
    createTenant(['id' => 'a2', 'name' => 'A2', 'email' => 'a2@test.com', 'plan' => SubscriptionTier::Growth, 'paused_at' => now()]);

    expect(Tenant::query()->count())->toBe(2);
});
