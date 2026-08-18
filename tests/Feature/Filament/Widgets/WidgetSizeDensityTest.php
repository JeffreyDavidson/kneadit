<?php

use App\Filament\Widgets\OrderFunnelWidget;
use App\Filament\Widgets\UpcomingOrdersWidget;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Cache::flush();
});

dataset('sizeDensityWidgets', [
    'upcoming orders (3 → 7 days)' => [UpcomingOrdersWidget::class],
    'order funnel (3 → all stages)' => [OrderFunnelWidget::class],
]);

test('widget renders denser content at large size than small size', function (string $widget) {
    $customer = Customer::factory()->create();

    foreach (range(0, 7) as $daysOut) {
        Order::factory()
            ->recycle($customer)
            ->confirmed()
            ->count(2)
            ->create([
                'delivery_date' => Date::today()->copy()->addDays($daysOut),
            ]);
    }

    $smallRows = countRows(Livewire::test($widget, ['dashboardSize' => 'sm'])->html());
    Cache::flush();
    $largeRows = countRows(Livewire::test($widget, ['dashboardSize' => 'lg'])->html());

    expect($largeRows)
        ->toBeGreaterThan(
            $smallRows,
            "{$widget} should render more list rows at lg ({$largeRows}) than sm ({$smallRows}) — if equal, the size prop is being ignored.",
        );
})->with('sizeDensityWidgets');

function countRows(string $html): int
{
    return substr_count($html, 'class="pw-row"');
}
