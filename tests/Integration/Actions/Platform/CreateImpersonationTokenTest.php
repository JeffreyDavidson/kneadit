<?php

use App\Actions\Platform\CreateImpersonationToken;
use App\Models\ImpersonationToken;
use App\Models\Tenant;

beforeEach(fn () => setUpCentralTest());

test('creates a hashed token in central database and returns URL', function () {
    $tenant = Tenant::factory()->create();

    $url = resolve(CreateImpersonationToken::class)($tenant);

    expect($url)->toBeString()->toContain('/impersonate/');
    expect(ImpersonationToken::query()->where('tenant_id', $tenant->id)->count())->toBe(1);
});
