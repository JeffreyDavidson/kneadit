<?php

use App\Enums\CouponType;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Models\Coupon;
use App\Models\User;
use Filament\Actions\CreateAction;
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
        ->assertHasNoActionErrors();

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
        ->assertHasActionErrors($errors);
})->with([
    'code is required' => [['code' => null], ['code' => 'required']],
    'type is required' => [['type' => null], ['type' => 'required']],
    'value is required' => [['value' => null], ['value' => 'required']],
]);

test('can edit a coupon via table action', function () {
    $coupon = Coupon::factory()->create();

    Livewire::test(ListCoupons::class)
        ->callTableAction('edit', $coupon, data: [
            'code' => 'UPDATED01',
            'type' => $coupon->type->value,
            'value' => $coupon->value,
            'is_active' => true,
        ])
        ->assertHasNoTableActionErrors();

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
