<?php

use App\Actions\Platform\CreateImpersonationToken;
use App\Models\Platform\ImpersonationToken;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Config;

beforeEach(fn () => setUpCentralTest());

test('creates a hashed token in central database and returns URL', function () {
    Config::set('app.url', 'http://kneadit.test:8000');
    $tenant = Tenant::factory()->create();

    $url = resolve(CreateImpersonationToken::class)($tenant);

    expect($url)->toStartWith("http://{$tenant->id}.kneadit.test:8000/impersonate/")
        ->and(ImpersonationToken::query()->where('tenant_id', $tenant->id)->count())->toBe(1);
});
