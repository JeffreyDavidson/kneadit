<?php

use App\Enums\Filament\WidgetSize;
use App\Filament\Shared\Dashboard\WidgetDefinition;
use App\Filament\Shared\Dashboard\WidgetMeta;
use App\Filament\Widgets\WelcomeBannerWidget;
use Filament\Support\Icons\Heroicon;

test('widget definitions use standard sizes when none are constrained', function () {
    $definition = new WidgetDefinition(
        class: WelcomeBannerWidget::class,
        name: 'Welcome',
        description: 'Welcome actions',
        icon: Heroicon::OutlinedBolt,
        defaultSize: WidgetSize::Small,
    );

    expect($definition->allowedSizes())->toBe(WidgetSize::standardSizes());
});

test('widget metadata exposes typed definitions', function () {
    $definition = WidgetMeta::all()['welcome_banner'];

    expect($definition)->toBeInstanceOf(WidgetDefinition::class)
        ->and($definition->class)->toBe(WelcomeBannerWidget::class)
        ->and(WidgetMeta::classFor('welcome_banner'))->toBe(WelcomeBannerWidget::class)
        ->and(WidgetMeta::has('welcome_banner'))->toBeTrue()
        ->and(WidgetMeta::has('missing_widget'))->toBeFalse()
        ->and(WidgetMeta::get('missing_widget'))->toBeNull();
});
