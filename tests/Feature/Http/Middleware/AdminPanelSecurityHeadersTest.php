<?php

use App\Http\Middleware\SecurityHeaders;
use App\Models\Platform\Tenant;
use Filament\Facades\Filament;

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['app.kneadit.test'], 'tenancy.tenant_domain' => 'kneadit.test']);
});

test('the bakery admin login page sends the frame, content-type and referrer headers', function () {
    $tenant = Tenant::factory()->create(['id' => 'headersbakery']);
    $tenant->domains()->create(['domain' => 'headersbakery.kneadit.test']);

    get('https://headersbakery.kneadit.test/admin/login')
        ->assertOk()
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

test('both admin panels add the security headers without the CSP', function (string $panel) {
    expect(Filament::getPanel($panel)->getMiddleware())
        ->toContain(SecurityHeaders::withoutCsp());
})->with(['admin', 'central']);
