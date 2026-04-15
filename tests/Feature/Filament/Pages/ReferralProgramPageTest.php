<?php

use App\Filament\Pages\Engagement\ReferralProgram;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;
use Livewire\Livewire;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;

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
    Livewire::test(ReferralProgram::class)
        ->assertOk();
});
