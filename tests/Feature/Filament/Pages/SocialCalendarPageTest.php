<?php

use App\Filament\Pages\Engagement\SocialCalendar;
use App\Models\Content\SocialPost;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Laravel\Pennant\Feature;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('social calendar opens on the bakery-local month at month end', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    // Wednesday 2026-09-30, 22:00 in New York; already October 1 in UTC.
    Date::setTestNow('2026-10-01 02:00');

    livewire(SocialCalendar::class)
        ->assertSet('year', 2026)
        ->assertSet('month', 9);
});

test('social calendar marks the bakery-local day as today', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');

    $days = collect(livewire(SocialCalendar::class)->instance()->calendarDays())
        ->filter(fn (?array $day): bool => $day !== null && $day['isToday']);

    expect($days)->toHaveCount(1)
        ->and($days->sole()['date'])->toBe('2026-10-05');
});

test('social calendar files a late-evening post under the bakery-local day', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-05 12:00');
    // 9 PM on Oct 5 in New York is 01:00 on Oct 6 in UTC.
    SocialPost::factory()->create(['scheduled_for' => Date::parse('2026-10-05 21:00', 'America/New_York')->utc()]);

    $component = livewire(SocialCalendar::class);

    expect(array_keys($component->get('posts')))->toBe(['2026-10-05'])
        ->and($component->get('posts')['2026-10-05'][0]['time'])->toBe('9:00 PM');
});

test('social calendar month boundaries follow the bakery time zone', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'Pacific/Auckland'])));
    Date::setTestNow('2026-10-15 12:00');
    // 06:00 on Oct 1 in Auckland is still Sep 30 in UTC; 00:30 on Nov 1 in Auckland is Oct 31 in UTC.
    SocialPost::factory()->create(['scheduled_for' => Date::parse('2026-10-01 06:00', 'Pacific/Auckland')->utc()]);
    SocialPost::factory()->create(['scheduled_for' => Date::parse('2026-10-31 23:00', 'Pacific/Auckland')->utc()]);
    SocialPost::factory()->create(['scheduled_for' => Date::parse('2026-11-01 00:30', 'Pacific/Auckland')->utc()]);

    $posts = livewire(SocialCalendar::class)->get('posts');

    expect(array_keys($posts))->toBe(['2026-10-01', '2026-10-31']);
});
