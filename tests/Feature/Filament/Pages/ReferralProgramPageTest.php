<?php

use App\Filament\Pages\Engagement\ReferralProgram;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->owner()->create());

    DB::table('tenants')->insert([
        'id' => 'test-bakery',
        'name' => 'Test Baker',
        'email' => 'baker@test.com',
        'plan' => 'pro',
        'store_name' => 'Test Bakery',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $tenant = Tenant::query()->find('test-bakery');
    app()->instance(TenantContract::class, $tenant);

    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

test('referral program page can render', function () {
    livewire(ReferralProgram::class)
        ->assertOk();
});

test('referral program page no longer promises a free month', function (string $text) {
    livewire(ReferralProgram::class)
        ->assertOk()
        ->assertDontSee($text);
})->with([
    'headline' => 'Earn 1 free month per referral',
    'promise' => 'free month',
    'months earned stat' => 'Months Earned',
]);

test('referral program page still shares the link and counts referrals', function () {
    livewire(ReferralProgram::class)
        ->assertOk()
        ->assertSee('Your Referral Link')
        ->assertSee('Total Referrals')
        ->assertSee('Completed');
});
