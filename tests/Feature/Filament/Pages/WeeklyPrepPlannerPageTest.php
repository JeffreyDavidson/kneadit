<?php

use App\Filament\Pages\Operations\WeeklyPrepPlanner;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
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

test('weekly prep planner shows the prep timeline for orders with recipes and survives a week change', function () {
    Date::setTestNow('2026-10-06 09:00');
    $product = Product::factory()->create(['name' => 'Country Sourdough']);
    Recipe::factory()->for($product)->create(['prep_time_minutes' => 45]);
    $order = Order::factory()->create(['delivery_date' => '2026-10-07', 'delivery_time' => '10:00']);
    OrderItem::factory()->for($order)->for($product)->create(['quantity' => 3]);
    Order::factory()->create(['delivery_date' => '2026-10-14', 'delivery_time' => '09:00']);

    livewire(WeeklyPrepPlanner::class)
        ->assertSee('Prep Timeline')
        ->assertSee('Start Country Sourdough (x3)')
        ->assertSee('09:15')
        ->set('selectedWeekStart', '2026-10-12')
        ->assertDontSee('Start Country Sourdough (x3)')
        ->assertSee('Wednesday, October 14');
});
