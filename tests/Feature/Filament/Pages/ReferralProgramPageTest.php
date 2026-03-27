<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Stancl\Tenancy\Contracts\Tenant;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());

    $fakeTenant = Mockery::mock(Tenant::class)->shouldIgnoreMissing();
    $fakeTenant->shouldReceive('getTenantKey')->andReturn('test-bakery');
    $fakeTenant->shouldReceive('getTenantKeyName')->andReturn('id');
    $fakeTenant->id = 'test-bakery';
    $fakeTenant->plan = 'pro';
    $fakeTenant->referral_code = 'TEST123';
    $fakeTenant->domains = collect([(object) ['domain' => 'test-bakery.getkneadit.test']]);

    app()->instance(Tenant::class, $fakeTenant);
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

// GenerateReferralCode action type-hints App\Models\Tenant (not interface),
// preventing Mockery tenant from working. Needs real tenancy initialization.
test('referral program page can render')
    ->todo();
