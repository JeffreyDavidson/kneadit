<?php

use App\Actions\Tenants\GenerateReferralCode;
use App\Models\Customers\Referral;
use App\Models\Platform\Tenant;

beforeEach(function () {
    setUpCentralTest();
});

function referralCodeTenant(mixed $id): Tenant
{
    throw_unless(is_string($id), RuntimeException::class, 'Expected a string tenant ID.');

    $tenant = Tenant::query()->find($id);

    if (! $tenant instanceof Tenant) {
        throw new RuntimeException('Expected one tenant.');
    }

    return $tenant;
}

test('it creates a new referral code for a tenant', function () {
    $row = createTenant(['store_name' => 'Test Bakery']);
    $tenant = referralCodeTenant($row->id);

    $code = resolve(GenerateReferralCode::class)($tenant);

    expect($code)->toBeString()->and(Referral::query()->where('referrer_tenant_id', $tenant->id)->count())->toBe(1);
});

test('it returns existing referral code if one exists', function () {
    $row = createTenant(['store_name' => 'Test Bakery']);
    $tenant = referralCodeTenant($row->id);

    $code1 = resolve(GenerateReferralCode::class)($tenant);
    $code2 = resolve(GenerateReferralCode::class)($tenant);

    expect($code1)->toBe($code2)->and(Referral::query()->where('referrer_tenant_id', $tenant->id)->count())->toBe(1);
});

test('it regenerates when existing code collides', function () {
    $row = createTenant(['store_name' => 'Test Bakery']);
    $tenant = referralCodeTenant($row->id);

    $otherRow = createTenant(['id' => 'other-bakery', 'name' => 'Other Owner', 'email' => 'other@test.com', 'store_name' => 'Other Bakery']);

    // Create a referral that has been used (so it won't match the unused query)
    Referral::factory()->create([
        'referrer_tenant_id' => $tenant->id,
        'referred_tenant_id' => $otherRow->id,
        'referred_email' => 'used@test.com',
        'referral_code' => 'test-bakery-xxxx',
    ]);

    $code = resolve(GenerateReferralCode::class)($tenant);

    expect($code)->toBeString()
        ->and(Referral::query()
            ->where('referrer_tenant_id', $tenant->id)
            ->whereNull('referred_tenant_id')
            ->count())->toBe(1);
});
