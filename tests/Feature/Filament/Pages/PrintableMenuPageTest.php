<?php

use App\Filament\Pages\Tools\PrintableMenu;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Stancl\Tenancy\Database\Models\Domain;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());

    $fakeTenant = new Tenant;
    $fakeTenant->forceFill([
        'id' => 'test-bakery',
        'custom_domain' => null,
        'plan' => 'pro',
        'store_name' => 'Test Bakery',
    ]);
    $fakeTenant->setRelation('domains', new Collection([
        new Domain(['domain' => 'test-bakery.getkneadit.test']),
    ]));

    app()->instance(TenantContract::class, $fakeTenant);
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

test('printable menu page can render', function () {
    livewire(PrintableMenu::class)
        ->assertOk();
});

test('the storefront link uses the subdomain, or the verified custom domain', function (bool $hasVerifiedDomain, string $expected) {
    config(['app.url' => 'https://app.getkneadit.app', 'tenancy.tenant_domain' => 'getkneadit.app']);
    $tenant = app(TenantContract::class);
    $tenant->forceFill(['id' => 'bakery-on-biscotto', 'subdomain' => 'bakeryonbiscotto']);

    if ($hasVerifiedDomain) {
        $tenant->forceFill(['custom_domain' => 'bakeryonbiscotto.com', 'custom_domain_verified_at' => now()]);
        DB::purge('central');
        $pdo = DB::connection('sqlite')->getPdo();
        DB::connection('central')->setPdo($pdo)->setReadPdo($pdo);
        createTenant(['id' => 'bakery-on-biscotto']);
        DB::table('domains')->insert([
            'domain' => 'bakeryonbiscotto.com',
            'tenant_id' => 'bakery-on-biscotto',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    expect(livewire(PrintableMenu::class)->instance()->getStorefrontUrl())->toBe($expected);
})->with([
    'no custom domain' => [false, 'https://bakeryonbiscotto.getkneadit.app'],
    'verified custom domain' => [true, 'https://bakeryonbiscotto.com'],
]);
