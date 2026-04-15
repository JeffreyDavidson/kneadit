<?php

use App\Filament\Pages\Settings\CustomDomain;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Stancl\Tenancy\Contracts\Tenant;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());

    $fakeTenant = Mockery::mock(Tenant::class)->shouldIgnoreMissing();
    $fakeTenant->shouldReceive('getTenantKey')->andReturn('test-bakery');
    $fakeTenant->shouldReceive('getTenantKeyName')->andReturn('id');
    $fakeTenant->id = 'test-bakery';
    $fakeTenant->plan = 'pro';
    $fakeTenant->custom_domain = null;

    app()->instance(Tenant::class, $fakeTenant);
});

test('custom domain page can render', function () {
    Livewire::test(CustomDomain::class)
        ->assertOk();
});
