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

test('setSpan clamps to 1..3', function () {
    $page = Livewire::test(DashboardConfig::class);

    $page->call('setSpan', 0, 5);
    expect($page->get('widgets')[0]['span'])->toBe(3);

    $page->call('setSpan', 0, -1);
    expect($page->get('widgets')[0]['span'])->toBe(1);
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

    expect($widgets)->toBe([RecentOrdersWidget::class])
        ->and($widgets)->not->toContain(WelcomeBannerWidget::class);
});
