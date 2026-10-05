<?php

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Pages\Analytics\ReportsCenter;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);

    $tenant = new Tenant;
    $tenant->forceFill([
        'id' => 'reports-center-export-test',
        'plan' => SubscriptionTier::Growth,
    ]);
    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize($tenant);
});

test('the CSV export neutralises spreadsheet formulas in report values', function (string $value) {
    $page = livewire(ReportsCenter::class)
        ->set('activeReport', 'custom')
        ->set('reportData', [
            'totalOrders' => 3,
            'topProducts' => [['name' => $value, 'revenue' => 12.5, 'units_sold' => 2]],
        ])
        ->call('exportCsv');

    $page->assertDispatched('export-csv', function (string $event, array $params) use ($value): bool {
        $name = $params['data']['topProducts'][0]['name'];

        return $name === "'{$value}"
            && $params['data']['totalOrders'] === 3
            && $params['data']['topProducts'][0]['revenue'] === 12.5
            && $params['type'] === 'custom';
    });
})->with([
    'hyperlink formula' => '=HYPERLINK("https://example.test","click")',
    'plus' => '+1+1',
    'minus' => '-2+3',
    'at sign' => '@SUM(A1)',
    'leading tab' => "\t=1+1",
    'leading carriage return' => "\r=1+1",
]);

test('the CSV export leaves ordinary values alone', function () {
    $page = livewire(ReportsCenter::class)
        ->set('activeReport', 'custom')
        ->set('reportData', ['topProducts' => [['name' => 'Sourdough = great', 'revenue' => -4.5]]])
        ->call('exportCsv');

    $page->assertDispatched('export-csv', fn (string $event, array $params): bool => $params['data']['topProducts'][0] === ['name' => 'Sourdough = great', 'revenue' => -4.5]);
});
