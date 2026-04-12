<?php

use App\Enums\Financial\CouponType;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Models\Financial\Coupon;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('can create a coupon via slide-over', function () {
    Livewire::test(ListCoupons::class)
        ->callAction(CreateAction::class, data: [
            'code' => 'SPRING20',
            'type' => CouponType::Percentage->value,
            'value' => 20,
            'is_active' => true,
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Coupon::class, [
        'code' => 'SPRING20',
        'value' => 20,
    ]);
});

test('create coupon validates required fields', function (array $data, array $errors) {
    Livewire::test(ListCoupons::class)
        ->callAction(CreateAction::class, data: [
            'code' => 'TEST01',
            'type' => CouponType::Percentage->value,
            'value' => 10,
            ...$data,
        ])
        ->assertHasFormErrors($errors);
})->with([
    'code is required' => [['code' => null], ['code' => 'required']],
    'type is required' => [['type' => null], ['type' => 'required']],
    'value is required' => [['value' => null], ['value' => 'required']],
]);

test('can edit a coupon via table action', function () {
    $coupon = Coupon::factory()->create();

    Livewire::test(ListCoupons::class)
        ->callAction(TestAction::make('edit')->table($coupon), data: [
            'code' => 'UPDATED01',
            'type' => $coupon->type->value,
            'value' => $coupon->value,
            'is_active' => true,
        ])
        ->assertHasNoFormErrors();

    expect($coupon->fresh()->code)->toBe('UPDATED01');
});

test('can search coupons by code', function () {
    $target = Coupon::factory()->create(['code' => 'SPRING20']);
    $other = Coupon::factory()->create(['code' => 'WINTER10']);

    Livewire::test(ListCoupons::class)
        ->searchTable('SPRING')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can render coupon table columns', function (string $column) {
    Coupon::factory()->create();

    Livewire::test(ListCoupons::class)
        ->assertCanRenderTableColumn($column);
})->with(['code', 'type', 'value', 'is_active']);

test('can filter coupons by type', function () {
    $percentage = Coupon::factory()->percentage()->create();
    $fixed = Coupon::factory()->fixed()->create();

    Livewire::test(ListCoupons::class)
        ->filterTable('type', CouponType::Percentage->value)
        ->assertCanSeeTableRecords(collect([$percentage]))
        ->assertCanNotSeeTableRecords(collect([$fixed]));
});

test('can sort coupons by code', function () {
    $alpha = Coupon::factory()->create(['code' => 'ALPHA01']);
    $zeta = Coupon::factory()->create(['code' => 'ZETA99']);

    Livewire::test(ListCoupons::class)
        ->sortTable('code')
        ->assertCanSeeTableRecords(collect([$alpha, $zeta]), inOrder: true)
        ->sortTable('code', 'desc')
        ->assertCanSeeTableRecords(collect([$zeta, $alpha]), inOrder: true);
});

test('resource returns globally searchable attributes', function () {
    expect(App\Filament\Resources\Coupons\CouponResource::getGloballySearchableAttributes())
        ->toBe(['code']);
});

test('resource returns global search result title', function () {
    $coupon = Coupon::factory()->create(['code' => 'SPRING20']);

    expect(App\Filament\Resources\Coupons\CouponResource::getGlobalSearchResultTitle($coupon))
        ->toBe('SPRING20');
});

test('resource returns global search result details', function () {
    $coupon = Coupon::factory()->percentage()->create(['value' => 20]);

    $details = App\Filament\Resources\Coupons\CouponResource::getGlobalSearchResultDetails($coupon);

    expect($details)
        ->toHaveKey('Type')
        ->toHaveKey('Value')
        ->toHaveKey('Active');
});
