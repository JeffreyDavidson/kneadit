<?php

use App\Filament\Pages\Dashboard\Dashboard;
use App\Filament\Pages\Dashboard\DashboardConfig;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\WelcomeBannerWidget;
use App\Models\Staff\User;
use App\Services\Settings\SettingsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('renders and saves the default layout', function () {
    Livewire::test(DashboardConfig::class)
        ->assertOk()
        ->call('save');

    expect(settings('dashboard_widgets'))->not->toBeNull();
});

test('reorder swaps two widgets in place', function () {
    $page = Livewire::test(DashboardConfig::class);
    $first = $page->get('widgets')[0];
    $second = $page->get('widgets')[1];

    $page->call('reorder', 0, 1);

    expect($page->get('widgets')[0])->toBe($second)
        ->and($page->get('widgets')[1])->toBe($first);
});

test('toggleWidget flips visibility', function () {
    $page = Livewire::test(DashboardConfig::class);
    $beforeVisible = $page->get('widgets')[0]['visible'];

    $page->call('toggleWidget', 0);

    expect($page->get('widgets')[0]['visible'])->toBe(! $beforeVisible);
});

test('setSize accepts t-shirt size strings, ignores unknown values, and rejects disallowed sizes', function () {
    $page = Livewire::test(DashboardConfig::class);

    // recent_orders allows all three sizes — sm → md transitions cleanly.
    $recentIndex = collect($page->get('widgets'))->search(fn ($w) => $w['key'] === 'recent_orders');
    $page->call('setSize', $recentIndex, 'md');
    expect($page->get('widgets')[$recentIndex]['size'])->toBe('md');

    // welcome_banner is locked to large — sm should be rejected and the size unchanged.
    $welcomeIndex = collect($page->get('widgets'))->search(fn ($w) => $w['key'] === 'welcome_banner');
    $sizeBefore = $page->get('widgets')[$welcomeIndex]['size'];
    $page->call('setSize', $welcomeIndex, 'sm');
    expect($page->get('widgets')[$welcomeIndex]['size'])->toBe($sizeBefore);

    // Unknown size strings on an unrestricted widget fall back to small.
    $page->call('setSize', $recentIndex, 'bogus');
    expect($page->get('widgets')[$recentIndex]['size'])->toBe('sm');
});

test('WidgetMeta::allowedSizesFor returns curated lists for known widgets and all sizes for unconstrained widgets', function () {
    expect(App\Filament\Shared\Dashboard\WidgetMeta::allowedSizesFor('welcome_banner'))
        ->toBe([App\Enums\Filament\WidgetSize::Large])
        ->and(App\Filament\Shared\Dashboard\WidgetMeta::allowedSizesFor('storefront_views'))
        ->toBe([App\Enums\Filament\WidgetSize::Small])
        ->and(App\Filament\Shared\Dashboard\WidgetMeta::allowedSizesFor('revenue_chart'))
        ->toBe([App\Enums\Filament\WidgetSize::Medium, App\Enums\Filament\WidgetSize::Large])
        // recent_orders has no allowedSizes key — defaults to all three sizes.
        ->and(App\Filament\Shared\Dashboard\WidgetMeta::allowedSizesFor('recent_orders'))
        ->toBe(App\Enums\Filament\WidgetSize::cases());
});

test('saved sizes that violate allowedSizes are clamped to the widget default on load', function () {
    // welcome_banner is locked to lg; a config saved before that constraint
    // landed could still have it set to sm. Loading should clamp to lg.
    resolve(SettingsManager::class)->set('dashboard_widgets', json_encode([
        'welcome_banner' => ['visible' => true, 'order' => 1, 'size' => 'sm'],
        'storefront_views' => ['visible' => true, 'order' => 2, 'size' => 'lg'],
    ]));

    $page = Livewire::test(DashboardConfig::class);
    $widgets = collect($page->get('widgets'))->keyBy('key');

    expect($widgets['welcome_banner']['size'])->toBe('lg')
        ->and($widgets['storefront_views']['size'])->toBe('sm');
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

    $page = Livewire::test(DashboardConfig::class);
    $widgets = collect($page->get('widgets'))->keyBy('key');

    expect($widgets['recent_orders']['size'])->toBe('sm')
        ->and($widgets['todays_orders']['size'])->toBe('md')
        ->and($widgets['stats_overview']['size'])->toBe('lg');
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
        fn ($w) => is_string($w) ? $w : $w->widget,
    )->all();

    expect($classes)->toBe([RecentOrdersWidget::class])
        ->and($classes)->not->toContain(WelcomeBannerWidget::class);
});

test('Dashboard::getWidgets pipes the saved size into widgets that use HasDashboardSize', function () {
    resolve(SettingsManager::class)->set('dashboard_widgets', json_encode([
        'recent_orders' => ['visible' => true, 'order' => 1, 'size' => 'lg'],
    ]));

    $widgets = (new Dashboard)->getWidgets();

    expect($widgets[0])->toBeInstanceOf(Filament\Widgets\WidgetConfiguration::class)
        ->and($widgets[0]->getProperties())->toBe(['dashboardSize' => 'lg']);
});
