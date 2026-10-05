<?php

use App\Enums\Financial\CouponType;
use App\Filament\Resources\Coupons\CouponResource;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Models\Financial\Coupon;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('can create a coupon via slide-over', function () {
    livewire(ListCoupons::class)
        ->callAction(CreateAction::class, data: [
            'code' => 'SPRING20',
            'type' => CouponType::Percentage->value,
            'percentage' => 20,
            'is_active' => true,
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(Coupon::class, [
        'code' => 'SPRING20',
        'percentage' => 20,
    ]);
});

test('create coupon validates required fields', function () {
    $cases = [
        [['code' => null], ['code' => 'required']],
        [['type' => null], ['type' => 'required']],
    ];

    foreach ($cases as [$data, $errors]) {
        livewire(ListCoupons::class)
            ->callAction(CreateAction::class, data: [
                'code' => 'TEST01',
                'type' => CouponType::Percentage->value,
                'percentage' => 10,
                ...$data,
            ])
            ->assertHasFormErrors($errors);
    }
});

test('create coupon requires the discount that matches its type', function (CouponType $type, string $field) {
    livewire(ListCoupons::class)
        ->callAction(CreateAction::class, data: [
            'code' => 'TEST01',
            'type' => $type->value,
        ])
        ->assertHasFormErrors([$field => 'required']);
})->with([
    'fixed' => [CouponType::Fixed, 'fixed_amount'],
    'percentage' => [CouponType::Percentage, 'percentage'],
]);

test('coupon validity dates are entered and shown in the bakery timezone', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));

    livewire(ListCoupons::class)
        ->callAction(CreateAction::class, data: [
            'code' => 'SPRING20',
            'type' => CouponType::Percentage->value,
            'percentage' => 20,
            'starts_at' => '2026-10-15 00:00:00',
            'expires_at' => '2026-10-15 23:59:00',
            'is_active' => true,
        ])
        ->assertHasNoFormErrors();
    $coupon = Coupon::query()->where('code', 'SPRING20')->firstOrFail();

    expect($coupon->starts_at->format('Y-m-d H:i'))->toBe('2026-10-15 04:00')
        ->and($coupon->expires_at->format('Y-m-d H:i'))->toBe('2026-10-16 03:59');

    livewire(ListCoupons::class)
        ->mountAction(TestAction::make('edit')->table($coupon))
        ->assertSet('mountedActions.0.data.starts_at', '2026-10-15 00:00:00')
        ->assertSet('mountedActions.0.data.expires_at', '2026-10-15 23:59:00');
});

test('can edit a coupon via table action', function () {
    $coupon = Coupon::factory()->percentage()->create();

    livewire(ListCoupons::class)
        ->callAction(TestAction::make('edit')->table($coupon), data: [
            'code' => 'UPDATED01',
            'type' => $coupon->type->value,
            'percentage' => $coupon->percentage->value(),
            'is_active' => true,
        ])
        ->assertHasNoFormErrors();

    expect($coupon->fresh()->code)->toBe('UPDATED01');
});

test('can search coupons by code', function () {
    $target = Coupon::factory()->create(['code' => 'SPRING20']);
    $other = Coupon::factory()->create(['code' => 'WINTER10']);

    livewire(ListCoupons::class)
        ->searchTable('SPRING')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can render coupon table columns', function () {
    Coupon::factory()->create();

    livewire(ListCoupons::class)
        ->assertCanRenderTableColumn('code')
        ->assertCanRenderTableColumn('type')
        ->assertCanRenderTableColumn('discount_value')
        ->assertCanRenderTableColumn('is_active');
});

test('can filter coupons by type', function () {
    $percentage = Coupon::factory()->percentage()->create();
    $fixed = Coupon::factory()->fixed()->create();

    livewire(ListCoupons::class)
        ->filterTable('type', CouponType::Percentage->value)
        ->assertCanSeeTableRecords(collect([$percentage]))
        ->assertCanNotSeeTableRecords(collect([$fixed]));
});

test('can sort coupons by code', function () {
    $alpha = Coupon::factory()->create(['code' => 'ALPHA01']);
    $zeta = Coupon::factory()->create(['code' => 'ZETA99']);

    livewire(ListCoupons::class)
        ->sortTable('code')
        ->assertCanSeeTableRecords(collect([$alpha, $zeta]), inOrder: true)
        ->sortTable('code', 'desc')
        ->assertCanSeeTableRecords(collect([$zeta, $alpha]), inOrder: true);
});

test('resource returns globally searchable attributes', function () {
    expect(CouponResource::getGloballySearchableAttributes())
        ->toBe(['code']);
});

test('resource returns global search result title', function () {
    $coupon = Coupon::factory()->create(['code' => 'SPRING20']);

    expect(CouponResource::getGlobalSearchResultTitle($coupon))
        ->toBe('SPRING20');
});

test('resource returns global search result details', function () {
    $coupon = Coupon::factory()->percentage()->create(['percentage' => 20]);

    $details = CouponResource::getGlobalSearchResultDetails($coupon);

    expect($details)
        ->toHaveKeys(['Type', 'Value', 'Active']);
});
