<?php

use App\Filament\Central\Pages\PromoCode;
use App\Models\Platform\PlatformPromoCode;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setUpCentralTest();
    actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('promo code page renders', function () {
    Livewire::test(PromoCode::class)->assertOk();
});

test('history list returns recent codes', function () {
    PlatformPromoCode::query()->create([
        'code' => 'CODE_A',
        'coupon_id' => 'c1',
        'promotion_code_id' => 'p1',
        'percent_off' => 10,
        'duration' => 'once',
        'max_redemptions' => 1,
    ]);
    PlatformPromoCode::query()->create([
        'code' => 'CODE_B',
        'coupon_id' => 'c2',
        'promotion_code_id' => 'p2',
        'percent_off' => 25,
        'duration' => 'once',
        'max_redemptions' => 1,
    ]);

    $page = new PromoCode;
    $codes = $page->getRecentCodes();

    expect($codes)->toHaveCount(2)
        ->and($codes->pluck('code')->all())->toContain('CODE_A', 'CODE_B');
});
