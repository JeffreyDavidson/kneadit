<?php

use App\Filament\Pages\Settings\CustomDomain;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Stancl\Tenancy\Database\Models\Domain;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->owner()->create());

    test()->tenant = Tenant::factory()->create(['plan' => 'pro']);
    app()->instance(TenantContract::class, test()->tenant);
});

test('saving a domain that belongs to another bakery shows a validation error and saves nothing', function () {
    Tenant::factory()->create()->createDomain(['domain' => 'taken.example.com']);

    livewire(CustomDomain::class)
        ->set('custom_domain', 'taken.example.com')
        ->call('save')
        ->assertHasErrors(['custom_domain']);

    expect(test()->tenant->refresh()->custom_domain)->toBeNull();
});

test('saving a pasted URL stores the normalized hostname', function () {
    livewire(CustomDomain::class)
        ->set('custom_domain', 'HTTPS://Shop.Example.com/')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('custom_domain', 'shop.example.com');

    expect(test()->tenant->refresh()->custom_domain)->toBe('shop.example.com')
        ->and(Domain::query()->where('domain', 'shop.example.com')->exists())->toBeTrue();
});
