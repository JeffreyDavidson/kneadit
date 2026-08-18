<?php

use App\Filament\Pages\Tools\QrCodeGenerator;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;
use Stancl\Tenancy\Contracts\Tenant;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());

    $fakeTenant = Mockery::mock(Tenant::class)->shouldIgnoreMissing();
    $fakeTenant->shouldReceive('getTenantKey')->andReturn('test-bakery');
    $fakeTenant->shouldReceive('getTenantKeyName')->andReturn('id');
    $fakeTenant->domains = collect([(object) ['domain' => 'test-bakery.getkneadit.test']]);
    $fakeTenant->id = 'test-bakery';
    $fakeTenant->plan = App\Enums\Platform\SubscriptionTier::Pro;

    app()->instance(Tenant::class, $fakeTenant);
    Feature::define('pro-features', fn () => true);
});

test('qr code generator page can render', function () {
    Livewire::test(QrCodeGenerator::class)
        ->assertOk();
});
