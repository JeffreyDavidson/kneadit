<?php

use App\Filament\Pages\Operations\WeeklyPrepPlanner;
use App\Models\Orders\Order;
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
    Feature::define('growth-features', fn () => true);
});

test('weekly prep planner badges the bakery-local day as today', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    // Sunday 2026-10-04, 22:00 in New York; already Monday in UTC.
    Date::setTestNow('2026-10-05 02:00');
    Order::factory()->create(['delivery_date' => '2026-10-02']);

    livewire(WeeklyPrepPlanner::class)
        ->set('selectedWeekStart', '2026-09-28')
        ->assertSee('Sunday, October 4')
        ->assertSee('Today');
});
