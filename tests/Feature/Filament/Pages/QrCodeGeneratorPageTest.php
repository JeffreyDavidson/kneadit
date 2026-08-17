<?php

use App\Filament\Pages\Tools\QrCodeGenerator;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Stancl\Tenancy\Database\Models\Domain;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());

    $fakeTenant = new Tenant;
    $fakeTenant->forceFill([
        'id' => 'test-bakery',
        'plan' => App\Enums\Platform\SubscriptionTier::Pro,
    ]);
    $fakeTenant->setRelation('domains', new Collection([
        new Domain(['domain' => 'test-bakery.getkneadit.test']),
    ]));

    app()->instance(TenantContract::class, $fakeTenant);
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

test('qr code generator page can render', function () {
    livewire(QrCodeGenerator::class)
        ->assertOk();
});
