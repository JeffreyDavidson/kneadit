<?php

use App\Filament\Pages\Tools\PrintableMenu;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
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

test('printable menu lists active categories with their active products only', function () {
    $breads = Category::factory()->create(['name' => 'Artisan Breads', 'sort_order' => 1]);
    $pastries = Category::factory()->create(['name' => 'Morning Pastries', 'sort_order' => 2]);
    Category::factory()->create(['name' => 'Empty Shelf', 'sort_order' => 3]);
    Category::factory()->inactive()->has(Product::factory(['name' => 'Hidden Category Loaf']), 'products')->create();
    Product::factory()->for($breads)->create(['name' => 'Country Sourdough']);
    Product::factory()->for($breads)->inactive()->create(['name' => 'Retired Rye']);
    Product::factory()->for($pastries)->create(['name' => 'Almond Croissant']);

    livewire(PrintableMenu::class)
        ->assertOk()
        ->assertSeeInOrder(['Artisan Breads', 'Country Sourdough', 'Morning Pastries', 'Almond Croissant'])
        ->assertDontSee(['Retired Rye', 'Empty Shelf', 'Hidden Category Loaf']);
});

test('printable menu can switch to the price list and modern layout', function () {
    $category = Category::factory()->create(['name' => 'Artisan Breads']);
    Product::factory()->for($category)->create(['name' => 'Country Sourdough']);

    livewire(PrintableMenu::class)
        ->call('setView', 'pricelist')
        ->call('setLayout', 'modern')
        ->assertSet('activeView', 'pricelist')
        ->assertSet('menuLayout', 'modern')
        ->assertSee('Country Sourdough');
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
