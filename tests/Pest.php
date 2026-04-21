<?php

use App\Enums\Platform\SubscriptionTier;
use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

pest()->extend(TestCase::class)
    /*
     * Lock RefreshDatabase to rollback the sqlite connection, regardless of
     * what config('database.default') is at teardown time.
     *
     * setUpTenantTest() points central's config at sqlite and shares the PDO.
     * Tests that trigger stancl/tenancy bootstrap (e.g. Tenant::factory()
     * ->create()) swap config('database.default') to 'tenant' mid-test, which
     * makes RefreshDatabase::connectionsToTransact() — which reads the default
     * dynamically — return ['tenant'] at teardown. sqlite's transaction is
     * never rolled back, and the next test's BEGIN throws "cannot start a
     * transaction within a transaction".
     *
     * Setting $this->connectionsToTransact = ['sqlite'] explicitly makes
     * property_exists() return true, so connectionsToTransact() returns our
     * value instead of the (default-dependent) fallback. sqlite always rolls
     * back cleanly.
     */
    ->beforeEach(function () {
        $this->connectionsToTransact = ['sqlite'];
    })
    ->in('Feature', 'Integration', 'Unit', 'Browser');

/*
|--------------------------------------------------------------------------
| Tenant Database Cleanup
|--------------------------------------------------------------------------
| Tenant::factory()->create() triggers Stancl's CreateDatabase job which
| writes real SQLite files to the database/ directory. These accumulate
| across test runs and cause TenantDatabaseAlreadyExistsException when
| a new test generates the same slug.
*/

beforeAll(function () {
    foreach (glob(database_path('tenant*')) ?: [] as $file) {
        if (is_file($file) && is_writable($file)) {
            unlink($file);
        }
    }
});

/*
|--------------------------------------------------------------------------
| Tenant Test Helpers
|--------------------------------------------------------------------------
*/

function setUpTenantTest(): void
{
    config(['database.connections.central' => config('database.connections.sqlite')]);
    config(['tenancy.central_domains' => []]);

    $tenantMigrationPath = database_path('migrations/tenant');

    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', [
            '--path' => $tenantMigrationPath,
            '--realpath' => true,
        ]);
    }
}

function tenantMiddleware(): array
{
    return [
        InitializeTenancyByDomainOrSubdomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureStorefrontEnabled::class,
        TrackPageView::class,
    ];
}

/*
|--------------------------------------------------------------------------
| Browser Test Helpers
|--------------------------------------------------------------------------
| authenticatedVisit() pipes a pre-captured Playwright storage state into
| visit() so admin browser tests skip the full login dance. Cuts per-test
| cost from ~14s (fill + click + two long waits + navigate) to ~3s.
|
| Regenerate the state with:
|   python3 tests/Browser/Helpers/prepare-admin-session.py
*/

/**
 * @return Pest\Browser\Api\PendingAwaitablePage
 */
function authenticatedVisit(string $url)
{
    return authenticatedVisitFor($url, 'tests/Browser/.admin-session.json');
}

function authenticatedCentralVisit(string $url)
{
    return authenticatedVisitFor($url, 'tests/Browser/.central-admin-session.json');
}

function authenticatedVisitFor(string $url, string $relativeSessionPath)
{
    $sessionPath = base_path($relativeSessionPath);

    if (! file_exists($sessionPath)) {
        throw new RuntimeException(
            "Session not found at {$sessionPath}. Generate it with: python3 tests/Browser/Helpers/prepare-admin-session.py",
        );
    }

    $state = json_decode((string) file_get_contents($sessionPath), true, flags: JSON_THROW_ON_ERROR);

    return visit($url, ['storageState' => $state]);
}

function fixtureId(string $key): int
{
    $path = base_path('tests/Browser/.admin-fixture-ids.json');

    if (! file_exists($path)) {
        throw new RuntimeException(
            "Fixture IDs not found at {$path}. Generate them with: python3 tests/Browser/Helpers/prepare-admin-session.py",
        );
    }

    $ids = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

    if (! isset($ids[$key])) {
        throw new RuntimeException("Fixture ID '{$key}' not found. Regenerate IDs with the prepare-admin-session script.");
    }

    return (int) $ids[$key];
}

/*
|--------------------------------------------------------------------------
| Central Test Helpers
|--------------------------------------------------------------------------
*/

function setUpCentralTest(): void
{
    config(['tenancy.central_domains' => ['localhost']]);
    config(['database.connections.central' => config('database.connections.sqlite')]);

    test()->artisan('migrate:fresh');

    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }

    createCentralTables();

    DB::purge('central');
    $pdo = DB::connection('sqlite')->getPdo();
    DB::connection('central')->setPdo($pdo)->setReadPdo($pdo);
}

function createCentralTables(): void
{
    if (! Schema::hasTable('tenants')) {
        Schema::create('tenants', function ($table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('email');
            $table->string('plan')->default('starter');
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('store_name')->nullable();
            $table->string('store_logo')->nullable();
            $table->string('brand_color_primary')->default('#d4920c');
            $table->string('brand_color_secondary')->default('#1c1410');
            $table->boolean('storefront_enabled')->default(true);
            $table->string('external_website')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('custom_domain')->nullable();
            $table->timestamps();
            $table->json('data')->nullable();
        });
    }

    if (! Schema::hasTable('domains')) {
        Schema::create('domains', function ($table) {
            $table->increments('id');
            $table->string('domain', 255)->unique();
            $table->string('tenant_id');
            $table->timestamps();
        });
    }

    $tables = [
        'platform_activities' => function ($table) {
            $table->id();
            $table->string('event');
            $table->string('tenant_id')->nullable();
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamps();
        },
        'admin_audit_logs' => function ($table) {
            $table->id();
            $table->unsignedInteger('admin_id')->nullable();
            $table->string('action');
            $table->text('description');
            $table->string('target_type')->nullable();
            $table->string('target_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        },
        'support_tickets' => function ($table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->string('status')->default('open');
            $table->string('priority')->default('normal');
            $table->text('admin_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        },
        'support_replies' => function ($table) {
            $table->id();
            $table->foreignId('ticket_id');
            $table->string('author_type');
            $table->string('author_name');
            $table->text('body');
            $table->timestamps();
        },
        'platform_messages' => function ($table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('sender_type')->default('admin');
            $table->string('subject');
            $table->text('body');
            $table->foreignId('parent_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        },
        'email_campaigns' => function ($table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->string('target_segment')->default('all');
            $table->string('status')->default('draft');
            $table->integer('recipient_count')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        },
        'platform_announcements' => function ($table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('type')->default('info');
            $table->json('target_plans')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        },
        'impersonation_tokens' => function ($table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('tenant_id');
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->nullable();
        },
        'blog_posts' => function ($table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('featured_image')->nullable();
            $table->string('category')->default('guides');
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        },
        'scheduled_checkins' => function ($table) {
            $table->id();
            $table->string('name');
            $table->integer('days_after_signup');
            $table->string('subject');
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        },
        'feature_usage_logs' => function ($table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('feature');
            $table->integer('usage_count')->default(1);
            $table->timestamp('last_used_at')->nullable();
            $table->date('date');
            $table->timestamp('created_at')->nullable();
        },
        'platform_settings' => function ($table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        },
        'tenant_notes' => function ($table) {
            $table->id();
            $table->string('tenant_id');
            $table->text('body');
            $table->string('author')->nullable();
            $table->timestamps();
        },
        'email_campaign_logs' => function ($table) {
            $table->id();
            $table->foreignId('campaign_id');
            $table->string('tenant_id');
            $table->string('email');
            $table->string('status')->default('sent');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();
        },
        'checkin_logs' => function ($table) {
            $table->id();
            $table->foreignId('checkin_id');
            $table->string('tenant_id');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        },
        'referrals' => function ($table) {
            $table->id();
            $table->string('referrer_tenant_id');
            $table->string('referred_tenant_id')->nullable();
            $table->string('referral_code')->unique();
            $table->string('referred_email')->nullable();
            $table->string('status')->default('pending');
            $table->integer('reward_months')->default(1);
            $table->timestamps();
        },
    ];

    foreach ($tables as $name => $callback) {
        if (! Schema::hasTable($name)) {
            Schema::create($name, function ($table) use ($callback) {
                $callback($table);
            });
        }
    }
}

function createTenant(array $attributes = []): object
{
    $defaults = [
        'id' => 'test-bakery',
        'name' => 'Test Owner',
        'email' => 'test@example.com',
        'plan' => SubscriptionTier::Starter,
        'is_active' => true,
        'storefront_enabled' => true,
        'brand_color_primary' => '#d4920c',
        'brand_color_secondary' => '#1c1410',
        'created_at' => now(),
        'updated_at' => now(),
    ];

    $data = array_merge($defaults, $attributes);
    DB::table('tenants')->insert($data);

    return DB::table('tenants')->where('id', $data['id'])->first();
}

/*
|--------------------------------------------------------------------------
| TenantSettings Builders
|--------------------------------------------------------------------------
| Construct TenantSettings (and its sub-DTOs) with sensible defaults so
| tests only override the fields they care about.
*/

/** @param array<string, mixed> $overrides */
function makeStoreInfo(array $overrides = []): App\DataTransferObjects\Settings\StoreInfo
{
    return new App\DataTransferObjects\Settings\StoreInfo(...array_merge([
        'name' => 'Test Bakery',
        'email' => null,
        'phone' => null,
        'address' => null,
        'website' => null,
        'photo' => null,
        'logo' => null,
        'tagline' => null,
    ], $overrides));
}

/** @param array<string, mixed> $overrides */
function makeBrandingSettings(array $overrides = []): App\DataTransferObjects\Settings\BrandingSettings
{
    return new App\DataTransferObjects\Settings\BrandingSettings(...array_merge([
        'brandColorPrimary' => '#d4920c',
        'storefrontTheme' => 'classic',
        'businessTagline' => null,
        'aboutUsText' => null,
        'heroImage' => null,
        'heroStyle' => 'split',
        'heroTagline' => null,
        'heroPrimaryCtaText' => 'Order Now',
        'heroSecondaryCtaText' => 'Browse Menu',
        'allergyDisclaimer' => null,
        'cateringHeroImage' => null,
        'loyaltyHeroImage' => null,
        'giftCardsHeroImage' => null,
    ], $overrides));
}

/** @param array<string, mixed> $overrides */
function makeOrderSettings(array $overrides = []): App\DataTransferObjects\Settings\OrderSettings
{
    return new App\DataTransferObjects\Settings\OrderSettings(...array_merge([
        'leadTimeHours' => 24,
        'deliveryEnabled' => true,
        'freeDeliveryMinimum' => '50',
        'minimumPickupOrderAmount' => '0',
        'minimumDeliveryOrderAmount' => '0',
        'deliveryFeeTiers' => [],
        'defaultDailyCapacity' => 20,
    ], $overrides));
}

/** @param array<string, mixed> $overrides */
function makeEngagementSettings(array $overrides = []): App\DataTransferObjects\Settings\EngagementSettings
{
    return new App\DataTransferObjects\Settings\EngagementSettings(...array_merge([
        'birthdayProgramEnabled' => false,
        'birthdayCouponEnabled' => false,
        'birthdayDiscountPercentage' => 15,
        'birthdayCouponValidDays' => 7,
        'reviewRequestsEnabled' => false,
        'reviewRequestDelayHours' => 24,
        'repeatRemindersEnabled' => false,
        'repeatReminderDays' => 30,
        'announcementEnabled' => false,
        'announcementText' => '',
        'announcementType' => 'info',
    ], $overrides));
}

/** @param array<string, mixed> $overrides */
function makePolicySettings(array $overrides = []): App\DataTransferObjects\Settings\PolicySettings
{
    return new App\DataTransferObjects\Settings\PolicySettings(...array_merge([
        'showOnStorefront' => false,
        'cancellation' => '',
        'deposit' => '',
        'refund' => '',
        'pickup' => '',
        'additionalTerms' => '',
    ], $overrides));
}

/** @param array<string, mixed> $overrides */
function makeHomepageSettings(array $overrides = []): App\DataTransferObjects\Settings\HomepageSettings
{
    return new App\DataTransferObjects\Settings\HomepageSettings(...array_merge([
        'socialMediaLinks' => [],
        'operatingHours' => [],
        'faqItems' => [],
        'sections' => [],
    ], $overrides));
}

/** @param array<string, mixed> $overrides */
function makeCateringSettings(array $overrides = []): App\DataTransferObjects\Settings\CateringSettings
{
    return new App\DataTransferObjects\Settings\CateringSettings(...array_merge([
        'enabled' => false,
        'minimumGuests' => '10',
        'leadTimeDays' => '14',
        'eventTypes' => ['Wedding', 'Corporate Event'],
    ], $overrides));
}

/** @param array<string, mixed> $overrides */
function makeLoyaltySettings(array $overrides = []): App\DataTransferObjects\Settings\LoyaltySettings
{
    return new App\DataTransferObjects\Settings\LoyaltySettings(...array_merge([
        'enabled' => true,
        'pointsPerDollar' => 10,
        'programName' => 'Rewards',
    ], $overrides));
}

/**
 * Build a TenantSettings with sensible defaults. Pass overrides for any
 * sub-DTO you need to customize; the rest will use the default builders.
 */
function makeTenantSettings(
    ?App\DataTransferObjects\Settings\StoreInfo $store = null,
    ?App\DataTransferObjects\Settings\BrandingSettings $branding = null,
    ?App\DataTransferObjects\Settings\OrderSettings $orders = null,
    ?App\DataTransferObjects\Settings\PaymentSettings $payment = null,
    ?App\DataTransferObjects\Settings\CateringSettings $catering = null,
    ?App\DataTransferObjects\Settings\LoyaltySettings $loyalty = null,
    ?App\DataTransferObjects\Settings\EngagementSettings $engagement = null,
    ?App\DataTransferObjects\Settings\PolicySettings $policies = null,
    ?App\DataTransferObjects\Settings\HomepageSettings $homepage = null,
    ?App\DataTransferObjects\Settings\OnboardingSettings $onboarding = null,
    ?App\DataTransferObjects\Settings\GiftCardSettings $giftCards = null,
): App\Services\Settings\TenantSettings {
    return new App\Services\Settings\TenantSettings(
        store: $store ?? makeStoreInfo(),
        branding: $branding ?? makeBrandingSettings(),
        orders: $orders ?? makeOrderSettings(),
        payment: $payment ?? new App\DataTransferObjects\Settings\PaymentSettings(methodsAccepted: []),
        catering: $catering ?? makeCateringSettings(),
        loyalty: $loyalty ?? makeLoyaltySettings(),
        engagement: $engagement ?? makeEngagementSettings(),
        policies: $policies ?? makePolicySettings(),
        homepage: $homepage ?? makeHomepageSettings(),
        onboarding: $onboarding ?? new App\DataTransferObjects\Settings\OnboardingSettings(completedAt: null),
        webhooks: new App\DataTransferObjects\Settings\WebhookSettings,
        giftCards: $giftCards ?? new App\DataTransferObjects\Settings\GiftCardSettings(
            presetAmounts: [10, 25, 50, 100],
            defaultAmount: 25,
        ),
    );
}
