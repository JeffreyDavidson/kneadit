<?php

use App\Actions\Tenants\CreateTenantRecord;
use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Date;
use Stancl\Tenancy\Exceptions\DomainOccupiedByOtherTenantException;

beforeEach(function () {
    setUpCentralTest();

    test()->user = User::factory()->owner()->create([
        'name' => 'Test Baker',
        'email' => 'baker@test.com',
    ]);
});

afterEach(function () {
    @unlink(database_path('recordbakery'));
    @unlink(database_path('recordownsite'));
});

it('creates an active starter tenant with a trial, contact details and a matching domain', function () {
    Date::setTestNow('2026-09-30 10:00');

    $tenant = resolve(CreateTenantRecord::class)(
        test()->user,
        'Record Bakery',
        'recordbakery',
        true,
        null,
    );

    expect($tenant)->toBeInstanceOf(Tenant::class)
        ->and($tenant->id)->toBe('recordbakery')
        ->and($tenant->name)->toBe('Test Baker')
        ->and($tenant->email)->toBe('baker@test.com')
        ->and($tenant->plan)->toBe(SubscriptionTier::Starter)
        ->and($tenant->store_name)->toBe('Record Bakery')
        ->and($tenant->is_active)->toBeTrue()
        ->and($tenant->trial_ends_at->toDateString())->toBe(now()->addDays(config('kneadit.trial_days', 30))->toDateString())
        ->and($tenant->domains->pluck('domain')->all())->toBe(['recordbakery']);
});

it('drops the external website when the KneadIt storefront is used and keeps it otherwise', function (bool $useKneadItStorefront, ?string $expected) {
    $subdomain = $useKneadItStorefront ? 'recordbakery' : 'recordownsite';

    $tenant = resolve(CreateTenantRecord::class)(
        test()->user,
        'Record Bakery',
        $subdomain,
        $useKneadItStorefront,
        'https://my-bakery.example.com',
    );

    expect($tenant->storefront_enabled)->toBe($useKneadItStorefront)
        ->and($tenant->external_website)->toBe($expected);
})->with([
    'kneadit storefront' => [true, null],
    'own website' => [false, 'https://my-bakery.example.com'],
]);

it('rolls back the tenant row when the domain is already taken', function () {
    createTenantWithDomain('squatter', 'recordbakery');

    expect(fn () => resolve(CreateTenantRecord::class)(test()->user, 'Record Bakery', 'recordbakery', true, null))
        ->toThrow(DomainOccupiedByOtherTenantException::class)
        ->and(Tenant::query()->whereKey('recordbakery')->exists())->toBeFalse();
});
