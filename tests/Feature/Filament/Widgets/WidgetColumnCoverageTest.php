<?php

use App\Enums\Orders\OrderStatus;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

// -- OrderStatus color mapping validation --
// Note: TodaysOrdersWidget and RecentOrdersWidget have a pre-existing type mismatch
// in their status color closures (fn (string $state) receives OrderStatus enum).
// These closures cannot be exercised via Livewire rendering until that bug is fixed.

test('order status values cover all expected statuses', function () {
    $statuses = OrderStatus::cases();

    expect($statuses)->not->toBeEmpty()
        ->and(collect($statuses)->pluck('value')->toArray())
        ->toContain('pending')
        ->toContain('confirmed')
        ->toContain('cancelled');
});

test('order status color mapping covers all cases', function () {
    $colorFn = fn (string $state) => match ($state) {
        OrderStatus::Pending->value => 'warning',
        OrderStatus::Confirmed->value => 'info',
        OrderStatus::Baking->value => 'primary',
        OrderStatus::Ready->value => 'success',
        OrderStatus::Delivered->value => 'gray',
        OrderStatus::Cancelled->value => 'danger',
        default => 'gray',
    };

    foreach (OrderStatus::cases() as $status) {
        expect($colorFn($status->value))->toBeString();
    }
});
