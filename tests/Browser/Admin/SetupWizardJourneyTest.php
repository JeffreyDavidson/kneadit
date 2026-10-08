<?php

use App\Console\Commands\Tenants\ProvisionTestTenantCommand;
use Database\Seeders\BrowserTestFixtureSeeder;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Pest\Browser\Playwright\Playwright;

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

/**
 * The served bakery reads and writes the fixture tenant's own SQLite file,
 * while this test process runs on an in-memory database. Reach the served
 * file through a connection of its own.
 */
function servedWizardTenantDatabase(): Connection
{
    config([
        'database.connections.served_wizard_tenant' => [
            ...config('database.connections.sqlite'),
            'database' => env('BROWSER_TEST_TENANT_DATABASE', database_path('tenant'.ProvisionTestTenantCommand::TENANT_ID)),
        ],
    ]);

    return DB::connection('served_wizard_tenant');
}

/** The served central database, which holds the fixture bakery's tenant row. */
function servedWizardCentralDatabase(): Connection
{
    config([
        'database.connections.served_wizard_central' => [
            ...config('database.connections.central'),
            'database' => env('BROWSER_TEST_CENTRAL_DATABASE', database_path('database.sqlite')),
        ],
    ]);

    return DB::connection('served_wizard_central');
}

function servedWizardSetting(string $key): ?string
{
    $value = servedWizardTenantDatabase()->table('settings')->where('key', $key)->value('value');

    return is_string($value) ? $value : null;
}

/**
 * Everything the wizard can change on the fixture bakery: its settings, the
 * products table (the wizard adds a first product) and its central tenant row.
 *
 * @return array{settings: list<array<string, mixed>>, max_product_id: int, tenant: array<string, mixed>}
 */
function snapshotServedWizardBakery(): array
{
    $tenant = servedWizardCentralDatabase()->table('tenants')->where('id', ProvisionTestTenantCommand::TENANT_ID)->first();

    throw_if($tenant === null, RuntimeException::class, 'The browser-test tenant is missing. Run tenants:provision-test-tenant first.');

    return [
        'settings' => servedWizardTenantDatabase()->table('settings')->get()->map(fn (object $row): array => (array) $row)->values()->all(),
        'max_product_id' => (int) servedWizardTenantDatabase()->table('products')->max('id'),
        'tenant' => (array) $tenant,
    ];
}

/** @param array{settings: list<array<string, mixed>>, max_product_id: int, tenant: array<string, mixed>} $snapshot */
function restoreServedWizardBakery(array $snapshot): void
{
    $logo = servedWizardSetting('store_logo');

    if ($logo !== null && $logo !== $snapshot['tenant']['store_logo']) {
        Storage::disk('public')->delete($logo);
        Storage::disk('local')->delete($logo);
    }

    servedWizardTenantDatabase()->transaction(function () use ($snapshot): void {
        servedWizardTenantDatabase()->table('settings')->delete();
        servedWizardTenantDatabase()->table('settings')->insert($snapshot['settings']);
        servedWizardTenantDatabase()->table('products')->where('id', '>', $snapshot['max_product_id'])->delete();
    });

    servedWizardCentralDatabase()->table('tenants')
        ->where('id', ProvisionTestTenantCommand::TENANT_ID)
        ->update($snapshot['tenant']);
}

// Walks a bakery owner through the whole bakery setup wizard in a real
// browser: the admin sends them to the setup page until setup is complete,
// every step is filled in and continued (with one step back), a logo is
// uploaded, and finishing lands on the dashboard with that logo showing.
//
// The fixture bakery has setup marked complete, so the test clears that in
// the served tenant database first and puts every setting, the new product
// and the tenant row back afterwards, so other tests and reruns start clean.
//
// Signing in and every Continue are full server round trips that save the
// step, so this test waits up to 15 seconds for each expectation instead of
// the default 5, and puts the default back afterwards.

test('a bakery owner completes the setup wizard and lands on the dashboard with their logo', function () use ($storefrontUrl) {
    $snapshot = snapshotServedWizardBakery();
    $continue = 'button:has-text("Continue"):visible';
    $currentStep = 'li.kn-onboarding-step[aria-current="step"]';

    $defaultTimeout = Playwright::timeout();

    servedWizardTenantDatabase()->table('settings')->where('key', 'onboarding_completed_at')->delete();
    pest()->browser()->timeout(15_000);

    try {
        $page = visit("{$storefrontUrl}/admin/login")
            ->fill('input[type="email"]', BrowserTestFixtureSeeder::ADMIN_EMAIL)
            ->fill('input[type="password"]', BrowserTestFixtureSeeder::ADMIN_PASSWORD)
            ->click('button[type="submit"]')
            ->waitForEvent('networkidle')
            ->assertPathIs('/admin/onboarding')
            ->assertSee('Set up your bakery')
            ->assertNotPresent('.fi-sidebar')
            ->assertNotPresent('.fi-topbar')
            ->assertSeeIn($currentStep, 'Welcome')
            ->assertSee('Step 1 of 10')
            ->assertNoJavaScriptErrors();

        // 1. Welcome
        $page
            ->fill('[id="content.welcome.bakery_name"]', 'Wizard Journey Bakery')
            ->fill('[id="content.welcome.owner_name"]', 'Wizard Journey Owner')
            ->click($continue)
            ->assertSeeIn($currentStep, 'Contact info')
            ->assertSee('Step 2 of 10');

        // 2. Contact info
        $page
            ->fill('[id="content.contact.email"]', 'wizard-journey@kneadit.test')
            ->fill('[id="content.contact.phone"]', '555-0142')
            ->fill('[id="content.contact.address"]', '1 Journey Lane, Portland, OR 97201')
            ->click($continue)
            ->assertSeeIn($currentStep, 'Branding')
            ->assertSee('Step 3 of 10');

        // Back returns to the previous step with its values kept.
        $page
            ->click('button:has-text("Back"):visible')
            ->assertSeeIn($currentStep, 'Contact info')
            ->assertSee('Step 2 of 10')
            ->assertValue('[id="content.contact.phone"]', '555-0142')
            ->click($continue)
            ->assertSeeIn($currentStep, 'Branding');

        // 3. Branding: upload a logo and wait for the upload to finish.
        $page
            ->attach('input.filepond--browser', base_path('tests/Browser/fixtures/bakery-logo.png'))
            ->assertPresent('.filepond--item[data-filepond-item-state="processing-complete"]')
            ->click($continue)
            ->assertSeeIn($currentStep, 'First product')
            ->assertSee('Step 4 of 10');

        // 4. First product
        $page
            ->fill('[id="content.product.name"]', 'Wizard Journey Sourdough')
            ->fill('[id="content.product.price"]', '12.50')
            ->select('[id="content.product.category_id"]', 'Browser Test Delivery')
            ->click($continue)
            ->assertSeeIn($currentStep, 'Business hours')
            ->assertSee('Step 5 of 10');

        // 5. Business hours: open on Saturday too (default 08:00 to 17:00).
        $page
            ->click('[id="content.hours.saturday"]')
            ->assertVisible('[id="content.hours.saturday_open"]')
            ->click($continue)
            ->assertSeeIn($currentStep, 'Compliance')
            ->assertSee('Step 6 of 10');

        // 6. Compliance, including the required confirmation.
        $page
            ->click('[id="content.compliance.cottage_food_state"]')
            ->click('li[role="option"]:has-text("Oregon"):visible')
            ->fill('[id="content.compliance.revenue_cap"]', '50000')
            ->fill('[id="content.compliance.allergy_disclaimer"]', 'Made in a home kitchen that also handles nuts, wheat and dairy.')
            ->check('[id="content.compliance.acknowledged"]')
            ->click($continue)
            ->assertSeeIn($currentStep, 'Delivery')
            ->assertSee('Step 7 of 10');

        // 7. Delivery: pickup is on by default and needs instructions.
        $page
            ->fill('[id="content.delivery.pickup_instructions"]', 'Ring the bell at the side door.')
            ->click($continue)
            ->assertSeeIn($currentStep, 'Payments')
            ->assertSee('Step 8 of 10');

        // 8. Payments: Cash / Manual is selected by default.
        $page
            ->assertChecked('input[type="checkbox"][value="cash"]')
            ->click($continue)
            ->assertSeeIn($currentStep, 'Preview')
            ->assertSee('Step 9 of 10')
            ->assertSee('Wizard Journey Bakery')
            ->assertSee('Wizard Journey Sourdough')
            ->assertScript('[...document.querySelectorAll(\'img[alt="Logo"]\')].some((logo) => logo.complete && logo.naturalWidth > 0)');

        // 9. Preview
        $page
            ->click($continue)
            ->assertSeeIn($currentStep, 'Complete')
            ->assertSee('Step 10 of 10');

        // 10. Complete
        $page
            ->click('button:has-text("Complete setup"):visible')
            ->waitForEvent('networkidle')
            ->assertPathIs('/admin')
            ->assertPresent('.fi-sidebar')
            ->assertPresent('.fi-topbar img.kn-brand-logo')
            ->assertScript('document.querySelector(".fi-topbar img.kn-brand-logo").complete && document.querySelector(".fi-topbar img.kn-brand-logo").naturalWidth > 0')
            ->assertNoJavaScriptErrors();

        // The browser decoded the logo above (naturalWidth > 0), which proves its URL served an image.
        expect(servedWizardSetting('onboarding_completed_at'))->not->toBeNull()
            ->and(servedWizardSetting('store_name'))->toBe('Wizard Journey Bakery')
            ->and(servedWizardSetting('store_phone'))->toBe('555-0142')
            ->and(servedWizardSetting('store_email'))->toBe('wizard-journey@kneadit.test')
            ->and(servedWizardSetting('cottage_food_state'))->toBe('OR')
            ->and(servedWizardSetting('compliance_acknowledged'))->toBe('1')
            ->and(servedWizardSetting('pickup_instructions'))->toBe('Ring the bell at the side door.')
            ->and(json_decode((string) servedWizardSetting('operating_hours'), true))->toMatchArray([
                'monday' => ['open' => '07:00', 'close' => '18:00'],
                'saturday' => ['open' => '08:00', 'close' => '17:00'],
            ])
            ->and(servedWizardTenantDatabase()->table('products')->where('name', 'Wizard Journey Sourdough')->exists())->toBeTrue();
    } finally {
        pest()->browser()->timeout($defaultTimeout);
        restoreServedWizardBakery($snapshot);
    }
})->group('launch-smoke');
