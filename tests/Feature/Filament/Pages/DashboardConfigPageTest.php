<?php

use App\Filament\Pages\Dashboard\Dashboard;
use App\Filament\Pages\Dashboard\DashboardConfig;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\TodaysOrdersWidget;
use App\Filament\Widgets\WelcomeBannerWidget;
use App\Models\Staff\User;
use App\Services\Settings\SettingsManager;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Livewire\Features\SupportTesting\Testable;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

/**
 * @param Testable<DashboardConfig> $page
 * @return list<array{key: string, visible: bool, size: string}>
 */
function dashboardConfigWidgets(Testable $page): array
{
    $widgets = $page->get('widgets');

    if (! is_array($widgets)) {
        throw new UnexpectedValueException('Dashboard widgets must be an array.');
    }

    return array_values(array_map(function (mixed $widget): array {
        if (! is_array($widget)) {
            throw new UnexpectedValueException('Each dashboard widget must be an array.');
        }

        return [
            'key' => Arr::string($widget, 'key'),
            'visible' => Arr::boolean($widget, 'visible'),
            'size' => Arr::string($widget, 'size'),
        ];
    }, $widgets));
}

/**
 * @param Testable<DashboardConfig> $page
 * @return array{key: string, visible: bool, size: string}
 */
function dashboardConfigWidget(Testable $page, string $key): array
{
    foreach (dashboardConfigWidgets($page) as $widget) {
        if ($widget['key'] === $key) {
            return $widget;
        }
    }

    throw new UnexpectedValueException("The dashboard widget [{$key}] is missing.");
}

test('renders and saves the default layout', function () {
    $page = livewire(DashboardConfig::class);

    $page->assertOk();
    $page->call('save');

    expect(settings('dashboard_widgets'))->not->toBeNull();
});

test('default layout keeps reporting widgets opt in', function () {
    Livewire::test(DashboardConfig::class)
        ->assertSet('widgets', fn (array $widgets): bool => collect($widgets)
            ->whereIn('key', ['weekly_revenue', 'top_products', 'customer_insights'])
            ->every(fn (array $widget): bool => $widget['visible'] === false));
});

test('reorder swaps two widgets in place', function () {
    $page = livewire(DashboardConfig::class);
    $first = dashboardConfigWidgets($page)[0];
    $second = dashboardConfigWidgets($page)[1];

    $page->call('reorder', 0, 1);

    expect(dashboardConfigWidgets($page)[0])->toBe($second)
        ->and(dashboardConfigWidgets($page)[1])->toBe($first);
});

test('toggleWidget flips visibility', function () {
    $page = livewire(DashboardConfig::class);
    $beforeVisible = dashboardConfigWidgets($page)[0]['visible'];

    $page->call('toggleWidget', 0);

    expect(dashboardConfigWidgets($page)[0]['visible'])->toBe(! $beforeVisible);
});

test('setSize accepts t-shirt size strings, ignores unknown values, and rejects disallowed sizes', function () {
    $page = livewire(DashboardConfig::class);

    // recent_orders allows all three sizes — sm → md transitions cleanly.
    $widgets = dashboardConfigWidgets($page);
    $recentIndex = collect($widgets)->search(fn (array $widget): bool => $widget['key'] === 'recent_orders');
    if ($recentIndex === false) {
        throw new UnexpectedValueException('The recent orders widget is missing.');
    }
    $page->call('setSize', $recentIndex, 'md');
    expect(dashboardConfigWidgets($page)[$recentIndex]['size'])->toBe('md');

    // welcome_banner is locked to large — sm should be rejected and the size unchanged.
    $welcomeIndex = collect($widgets)->search(fn (array $widget): bool => $widget['key'] === 'welcome_banner');
    if ($welcomeIndex === false) {
        throw new UnexpectedValueException('The welcome banner widget is missing.');
    }
    $sizeBefore = dashboardConfigWidgets($page)[$welcomeIndex]['size'];
    $page->call('setSize', $welcomeIndex, 'sm');
    expect(dashboardConfigWidgets($page)[$welcomeIndex]['size'])->toBe($sizeBefore);

    // Unknown size strings on an unrestricted widget fall back to small.
    $page->call('setSize', $recentIndex, 'bogus');
    expect(dashboardConfigWidgets($page)[$recentIndex]['size'])->toBe('sm');
});

test('WidgetMeta::allowedSizesFor returns curated lists for known widgets and standard sizes for unconstrained widgets', function () {
    expect(App\Filament\Shared\Dashboard\WidgetMeta::allowedSizesFor('welcome_banner'))
        ->toBe([App\Enums\Filament\WidgetSize::Small, App\Enums\Filament\WidgetSize::Medium])
        ->and(App\Filament\Shared\Dashboard\WidgetMeta::allowedSizesFor('storefront_views'))
        ->toBe([App\Enums\Filament\WidgetSize::Small])
        ->and(App\Filament\Shared\Dashboard\WidgetMeta::allowedSizesFor('revenue_chart'))
        ->toBe([App\Enums\Filament\WidgetSize::Medium, App\Enums\Filament\WidgetSize::Large, App\Enums\Filament\WidgetSize::ExtraLarge])
        // recent_orders has no allowedSizes key — defaults to standard sizes (sm/md/lg, no XL).
        ->and(App\Filament\Shared\Dashboard\WidgetMeta::allowedSizesFor('recent_orders'))
        ->toBe(App\Enums\Filament\WidgetSize::standardSizes());
});

test('saved sizes that violate allowedSizes are clamped to the widget default on load', function () {
    // welcome_banner allows [sm, md] with default sm; a config persisted with
    // an out-of-range size like 'xl' should clamp back to the sm default.
    // storefront_views only allows sm — a stored 'lg' should clamp to sm.
    resolve(SettingsManager::class)->set('dashboard_widgets', json_encode([
        'welcome_banner' => ['visible' => true, 'order' => 1, 'size' => 'xl'],
        'storefront_views' => ['visible' => true, 'order' => 2, 'size' => 'lg'],
    ]));

    $page = livewire(DashboardConfig::class);
    expect(dashboardConfigWidget($page, 'welcome_banner')['size'])->toBe('sm')
        ->and(dashboardConfigWidget($page, 'storefront_views')['size'])->toBe('sm');
});

test('legacy integer span values are migrated to t-shirt sizes on load', function () {
    // Use widgets whose allowedSizes accept the migrated value so the
    // legacy → modern mapping is observable. (See the clamping test
    // above for what happens when the migrated value is disallowed.)
    resolve(SettingsManager::class)->set('dashboard_widgets', json_encode([
        'recent_orders' => ['visible' => true, 'order' => 1, 'span' => 1],
        'todays_orders' => ['visible' => true, 'order' => 2, 'span' => 2],
        'stats_overview' => ['visible' => true, 'order' => 3, 'span' => 3],
    ]));

    $page = livewire(DashboardConfig::class);
    expect(dashboardConfigWidget($page, 'recent_orders')['size'])->toBe('sm')
        ->and(dashboardConfigWidget($page, 'todays_orders')['size'])->toBe('md')
        ->and(dashboardConfigWidget($page, 'stats_overview')['size'])->toBe('lg');
});

test('Dashboard::getWidgets returns the full registry when no saved layout exists', function () {
    expect(settings('dashboard_widgets'))->toBeNull();

    $widgets = (new Dashboard)->getWidgets();

    expect($widgets)->not->toBeEmpty();
});

test('Dashboard::getWidgets honors saved order and visibility', function () {
    resolve(SettingsManager::class)->set('dashboard_widgets', json_encode([
        'recent_orders' => ['visible' => true, 'order' => 1, 'span' => 1],
        'welcome_banner' => ['visible' => false, 'order' => 2, 'span' => 3],
    ]));

    $widgets = (new Dashboard)->getWidgets();

    // Widgets that use HasDashboardSize get wrapped in WidgetConfiguration so
    // their dashboardSize property gets piped through Livewire mount.
    $classes = collect($widgets)->map(
        fn (string|WidgetConfiguration $widget): string => is_string($widget) ? $widget : $widget->widget,
    )->all();

    // recent_orders is the only saved-visible widget, so it leads the list.
    // The remaining slots are non-default-hidden registry widgets surfaced
    // automatically so newly-shipped widgets appear for tenants with an
    // older saved layout — see Dashboard::getWidgets().
    expect($classes[0])->toBe(RecentOrdersWidget::class)
        ->and($classes)->not->toContain(WelcomeBannerWidget::class);
});

test('Dashboard::getWidgets pipes the saved size into widgets that use HasDashboardSize', function () {
    // todays_orders allows [md, lg] and uses HasDashboardSize, so a saved
    // size flows through wrapWithSize into a WidgetConfiguration.
    resolve(SettingsManager::class)->set('dashboard_widgets', json_encode([
        'todays_orders' => ['visible' => true, 'order' => 1, 'size' => 'lg'],
    ]));

    $widgets = (new Dashboard)->getWidgets();

    $widget = $widgets[0];
    if (! $widget instanceof WidgetConfiguration) {
        throw new UnexpectedValueException('The configured widget must carry dashboard properties.');
    }

    expect($widget->widget)->toBe(TodaysOrdersWidget::class)
        ->and($widget->getProperties())->toBe(['dashboardSize' => 'lg']);
});
