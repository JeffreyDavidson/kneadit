<?php

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Widgets\AnnouncementBanner;
use App\Models\Platform\PlatformAnnouncement;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('announcement banner widget can render', function () {
    livewire(AnnouncementBanner::class)
        ->assertOk();
});

test('announcement banner widget shows an active announcement again when it comes back from a serializing cache', function () {
    useSerializingCache();
    PlatformAnnouncement::factory()->create(['title' => 'Planned maintenance tonight']);

    livewire(AnnouncementBanner::class)->assertOk();
    livewire(AnnouncementBanner::class)
        ->assertOk()
        ->assertSee('Planned maintenance tonight');
});

test('announcement banner widget shows a plan-targeted announcement only to tenants on that plan', function (SubscriptionTier $plan, bool $shown) {
    PlatformAnnouncement::factory()->create(['title' => 'Pro only news', 'target_plans' => [SubscriptionTier::Pro->value]]);
    $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['plan' => $plan]));
    Filament::setTenant($tenant);

    $component = livewire(AnnouncementBanner::class);

    $shown ? $component->assertSee('Pro only news') : $component->assertDontSee('Pro only news');
})->with([
    'pro tenant' => [SubscriptionTier::Pro, true],
    'starter tenant' => [SubscriptionTier::Starter, false],
]);
