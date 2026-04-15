<?php

use App\Models\Customers\Referral;
use App\Models\Platform\Tenant;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('referral link stores code in session and redirects to register', function () {
    $tenant = Tenant::factory()->create();
    $referral = Referral::factory()->create([
        'referrer_tenant_id' => $tenant->id,
        'referral_code' => 'test-bakery-abc1',
    ]);

    $this->get(route('referral.track', 'test-bakery-abc1'))
        ->assertRedirect(route('register'))
        ->assertSessionHas('referral_code', 'test-bakery-abc1');
});
