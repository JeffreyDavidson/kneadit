<?php

use App\Filament\Pages\PrintableMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Stancl\Tenancy\Contracts\Tenant;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());

    $fakeTenant = Mockery::mock(Tenant::class)->shouldIgnoreMissing();
    $fakeTenant->shouldReceive('getTenantKey')->andReturn('test-bakery');
    $fakeTenant->shouldReceive('getTenantKeyName')->andReturn('id');
    $fakeTenant->domains = collect([(object) ['domain' => 'test-bakery.getkneadit.test']]);
    $fakeTenant->custom_domain = null;
    $fakeTenant->plan = 'pro';
    $fakeTenant->store_name = 'Test Bakery';
    $fakeTenant->id = 'test-bakery';

    app()->instance(Tenant::class, $fakeTenant);
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

test('printable menu page can render', function () {
    Livewire::test(PrintableMenu::class)
        ->assertOk();
});
