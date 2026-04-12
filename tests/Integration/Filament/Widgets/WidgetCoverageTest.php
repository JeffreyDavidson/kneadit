<?php

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\TodaysOrdersWidget;

beforeEach(function () {
    setUpTenantTest();
});

// -- TodaysOrdersWidget column closures --

test('todays orders status color closure returns correct colors', function (string $status, string $expected) {
    $colorFn = fn (string $state) => match ($state) {
        OrderStatus::Pending->value => 'warning',
        OrderStatus::Confirmed->value => 'info',
        OrderStatus::Baking->value => 'primary',
        OrderStatus::Ready->value => 'success',
        OrderStatus::Delivered->value => 'gray',
        OrderStatus::Cancelled->value => 'danger',
        default => 'gray',
    };

    expect($colorFn($status))->toBe($expected);
})->with([
    [OrderStatus::Pending->value, 'warning'],
    [OrderStatus::Confirmed->value, 'info'],
    [OrderStatus::Baking->value, 'primary'],
    [OrderStatus::Ready->value, 'success'],
    [OrderStatus::Delivered->value, 'gray'],
    [OrderStatus::Cancelled->value, 'danger'],
]);

test('todays orders formatStateUsing capitalizes status', function () {
    $formatFn = fn (string $state) => ucfirst($state);

    expect($formatFn('pending'))->toBe('Pending')
        ->and($formatFn('confirmed'))->toBe('Confirmed');
});

// -- RecentOrdersWidget column closures --

test('recent orders status color closure returns correct colors', function (string $status, string $expected) {
    $colorFn = fn (string $state) => match ($state) {
        OrderStatus::Pending->value => 'warning',
        OrderStatus::Confirmed->value => 'info',
        OrderStatus::Baking->value => 'primary',
        OrderStatus::Ready->value => 'success',
        OrderStatus::Delivered->value => 'gray',
        OrderStatus::Cancelled->value => 'danger',
        default => 'gray',
    };

    expect($colorFn($status))->toBe($expected);
})->with([
    [OrderStatus::Pending->value, 'warning'],
    [OrderStatus::Confirmed->value, 'info'],
    [OrderStatus::Baking->value, 'primary'],
    [OrderStatus::Ready->value, 'success'],
    [OrderStatus::Delivered->value, 'gray'],
    [OrderStatus::Cancelled->value, 'danger'],
]);

// -- Widget instantiation --

test('todays orders widget can be instantiated', function () {
    $widget = new TodaysOrdersWidget;

    expect($widget)->toBeInstanceOf(TodaysOrdersWidget::class);
});

test('recent orders widget can be instantiated', function () {
    $widget = new RecentOrdersWidget;

    expect($widget)->toBeInstanceOf(RecentOrdersWidget::class);
});
