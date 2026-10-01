<?php

use App\Filament\Pages\Engagement\SocialCalendar;
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
