<?php

use App\Enums\Platform\SubscriptionTier;
use App\Events\Platform\TenantOnboarded;
use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use App\Listeners\Platform\NotifyPlatformOfNewTenantListener;
use App\Listeners\Platform\SendWelcomeBakerEmailListener;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

/*
 * Tenant::factory()->create() dispatches stancl/tenancy's TenantCreated
 * event, which runs CreateDatabase + MigrateDatabase jobs that write real
 * SQLite files at database/tenant{id}. SignupPipelineTest depends on those
 * files being real, so the event can't just be faked globally — instead
 * the afterEach hook below sweeps them up so they don't accumulate.
 *
 * Browser-test fixture tenants (provisioned by tenants:provision-test-tenant)
 * are persistent and must survive between test runs — those are skipped.
 */
$persistentTenantDbs = [
    'tenantbrowser-test',
    'tenantdemo',
];

$cleanupTenantFiles = function () use ($persistentTenantDbs): void {
    if (function_exists('tenancy') && tenancy()->initialized) {
        tenancy()->end();
    }

    DB::purge('tenant');

    gc_collect_cycles();
    foreach (glob(database_path('tenant*')) ?: [] as $file) {
        if (! is_file($file)) {
            continue;
        }

        if (in_array(basename($file), $persistentTenantDbs, true)) {
            continue;
        }

        @unlink($file);
        @unlink($file . '-journal');
        @unlink($file . '-wal');
        @unlink($file . '-shm');
    }
};

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
        test()->connectionsToTransact = ['sqlite'];
    })
    ->afterEach($cleanupTenantFiles)
    ->in('Feature', 'Integration', 'Unit', 'Browser');

pest()->tia()
    ->always()
    ->locally()
    ->filtered();

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

/**
 * @template TComponent of Component
 *
 * @param class-string<TComponent> $component
 * @param array<string, mixed> $parameters
 * @return Testable<TComponent>
 */
function livewire(string $component, array $parameters = []): Testable
{
    return Livewire::test($component, $parameters);
}

/** @return list<class-string> */
function tenantMiddleware(): array
{
    return [
        InitializeTenancyByDomainOrSubdomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureStorefrontEnabled::class,
        TrackPageView::class,
    ];
}

/**
 * Session payload that grants the EnsureOrderAccess middleware permission
 * to view the given orders. Compose with ->withSession(...).
 *
 * @param array<int, App\Models\Orders\Order> $orders
 * @return array<string, array<int, string>>
 */
function verifiedOrdersSession(array $orders): array
{
    return [
        'verified_order_numbers' => array_map(fn ($order) => $order->order_number, $orders),
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
| The session file expires every two hours (Laravel session lifetime).
| When the helper detects a stale or missing session it transparently
| re-runs prepare-admin-session.mjs — first test pays the login cost,
| every subsequent test uses the warm session.
*/

/**
 * @return Pest\Browser\Api\PendingAwaitablePage
 */
function authenticatedVisit(string $url)
{
    return authenticatedVisitFor($url, 'tests/Browser/.admin-session.json');
}

function authenticatedCentralVisit(string $url): Pest\Browser\Api\PendingAwaitablePage
{
    return authenticatedVisitFor($url, 'tests/Browser/.central-admin-session.json');
}

function authenticatedVisitFor(string $url, string $relativeSessionPath): Pest\Browser\Api\PendingAwaitablePage
{
    ensureFreshAdminSessions(base_path($relativeSessionPath));

    $state = json_decode((string) file_get_contents(base_path($relativeSessionPath)), true, flags: JSON_THROW_ON_ERROR);

    return visit($url, ['storageState' => $state]);
}

function fixtureId(string $key): int
{
    $path = base_path('tests/Browser/.admin-fixture-ids.json');

    ensureFreshAdminSessions($path);

    $ids = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

    throw_unless(is_array($ids) && isset($ids[$key]) && is_int($ids[$key]), RuntimeException::class, "Fixture ID '{$key}' not found in {$path}. The prepare-admin-session script may need updating.");

    return $ids[$key];
}

/**
 * Ensure the admin browser session and fixture-id files are present and
 * not yet expired. Re-runs the Playwright login helper transparently when
 * either condition fails.
 */
function ensureFreshAdminSessions(string $referencedPath): void
{
    if (file_exists($referencedPath) && ! adminSessionIsStale()) {
        return;
    }

    $script = base_path('tests/Browser/Helpers/prepare-admin-session.mjs');

    throw_unless(file_exists($script), RuntimeException::class, "Login helper missing: {$script}");

    $output = [];
    $exitCode = 0;
    exec('node ' . escapeshellarg($script) . ' 2>&1', $output, $exitCode);

    if ($exitCode !== 0) {
        throw new RuntimeException(
            "Failed to refresh admin browser session (exit {$exitCode}):\n" . implode("\n", $output),
        );
    }
}

function adminSessionIsStale(): bool
{
    foreach (['tests/Browser/.admin-session.json', 'tests/Browser/.central-admin-session.json'] as $rel) {
        $path = base_path($rel);

        if (! file_exists($path)) {
            return true;
        }

        $state = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $now = time();

        if (! is_array($state)) {
            return true;
        }

        $cookies = $state['cookies'] ?? [];

        if (! is_array($cookies)) {
            return true;
        }

        foreach ($cookies as $cookie) {
            if (! is_array($cookie)) {
                return true;
            }

            $expires = $cookie['expires'] ?? 0;

            if (! is_int($expires) && ! is_float($expires)) {
                return true;
            }

            // Treat any cookie within 60s of expiry as already stale to
            // avoid a session expiring mid-test.
            if ($expires > 0 && $expires <= $now + 60) {
                return true;
            }
        }
    }

    return false;
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
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->string('consumer_ip', 45)->nullable();
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
        'platform_promo_codes' => function ($table) {
            $table->id();
            $table->string('code');
            $table->string('coupon_id');
            $table->string('promotion_code_id');
            $table->unsignedTinyInteger('percent_off')->nullable();
            $table->unsignedInteger('amount_off_cents')->nullable();
            $table->string('duration');
            $table->unsignedTinyInteger('duration_in_months')->nullable();
            $table->unsignedInteger('max_redemptions')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->string('tenant_id')->nullable();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
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

    if (Schema::hasTable('blog_posts') && ! Schema::hasColumn('blog_posts', 'category')) {
        Schema::table('blog_posts', function ($table) {
            $table->string('category')->default('guides');
        });
    }
}

/** @param array<string, mixed> $attributes */
function createTenant(array $attributes = []): stdClass
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

    $tenant = DB::table('tenants')->where('id', $data['id'])->first();

    if (! $tenant instanceof stdClass) {
        throw new RuntimeException('The tenant fixture could not be loaded after creation.');
    }

    return $tenant;
}

/**
 * Create a central tenant row plus its primary domain/subdomain record.
 * Use this in behavior tests instead of opaque Tenant::factory() arrays.
 *
 * @param array<string, mixed> $attributes
 */
function createTenantWithDomain(
    string $tenantId = 'test-bakery',
    ?string $domain = null,
    array $attributes = [],
): Tenant {
    createTenant([
        'id' => $tenantId,
        'store_name' => str($tenantId)->replace('-', ' ')->title()->toString(),
        ...$attributes,
    ]);

    $domain ??= "{$tenantId}.kneadit.test";

    DB::table('domains')->updateOrInsert(
        ['domain' => $domain],
        [
            'tenant_id' => $tenantId,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    );

    return Tenant::query()->findOrFail($tenantId);
}

/**
 * Register a signup-style visitor as a tenant and queue the onboarding mail.
 * Returns the created central user, tenant model, and storefront domain.
 *
 * @param array<string, mixed> $userAttributes
 * @param array<string, mixed> $tenantAttributes
 * @return array{user: User, tenant: Tenant, domain: string, admin_url: string}
 */
function registerVisitorAsTenant(
    array $userAttributes = [],
    array $tenantAttributes = [],
    ?string $tenantId = null,
    ?string $domain = null,
): array {
    $tenantName = $tenantAttributes['store_name'] ?? $userAttributes['name'] ?? 'test-bakery';

    if (! is_string($tenantName)) {
        throw new InvalidArgumentException('The tenant fixture name must be a string.');
    }

    $tenantId ??= str($tenantName)
        ->slug()
        ->append('-', str()->random(6))
        ->toString();

    $domain ??= "{$tenantId}.kneadit.test";

    $user = User::factory()->create([
        'name' => 'Test Baker',
        'email' => 'baker@example.com',
        ...$userAttributes,
    ]);

    $tenant = createTenantWithDomain($tenantId, $domain, [
        'name' => $user->name,
        'email' => $user->email,
        ...$tenantAttributes,
    ]);

    $adminUrl = "https://{$domain}/admin";
    queueTenantOnboardingNotifications($user, $tenant, $adminUrl);

    return [
        'user' => $user,
        'tenant' => $tenant,
        'domain' => $domain,
        'admin_url' => $adminUrl,
    ];
}

/**
 * Create and authenticate a tenant-admin user in the current tenant test DB.
 * Call setUpTenantTest() first when the test is not bootstrapping tenancy via HTTP.
 *
 * @param array<string, mixed> $attributes
 */
function actingAsTenantAdmin(?Tenant $tenant = null, array $attributes = []): User
{
    $admin = User::factory()->owner()->create([
        'name' => 'Tenant Admin',
        'email' => $tenant ? "admin@{$tenant->id}.test" : 'tenant-admin@example.com',
        ...$attributes,
    ]);

    test()->actingAs($admin);

    return $admin;
}

/**
 * Visit a tenant storefront route with the correct Host header so feature tests
 * exercise domain/subdomain tenancy middleware instead of hard-coded URLs.
 *
 * @param array<string, string> $headers
 * @return TestResponse<Symfony\Component\HttpFoundation\Response>
 */
function visitStorefrontAsTenant(Tenant $tenant, string $path = '/', array $headers = []): TestResponse
{
    $domain = DB::table('domains')->where('tenant_id', $tenant->id)->value('domain')
        ?? "{$tenant->id}.kneadit.test";

    if (! is_string($domain)) {
        throw new RuntimeException('The tenant domain must be a string.');
    }

    $url = "https://{$domain}/" . ltrim($path, '/');

    return test()->get($url, $headers);
}

function queueTenantOnboardingNotifications(User $user, Tenant $tenant, ?string $adminUrl = null): void
{
    config(['mail.platform_notify' => config('mail.platform_notify') ?: 'platform@example.com']);

    $domain = DB::table('domains')->where('tenant_id', $tenant->id)->value('domain')
        ?? "{$tenant->id}.kneadit.test";

    if (! is_string($domain)) {
        throw new RuntimeException('The tenant domain must be a string.');
    }

    $event = new TenantOnboarded(
        user: $user,
        tenant: $tenant,
        adminUrl: $adminUrl ?? "https://{$domain}/admin",
    );

    (new SendWelcomeBakerEmailListener)->handle($event);
    (new NotifyPlatformOfNewTenantListener)->handle($event);
}

/**
 * Assert a mailable notification was queued. This keeps behavior tests focused
 * on the business notification instead of the Mail fake's implementation API.
 */
function assertNotificationQueued(string $mailableClass, ?callable $callback = null): void
{
    if ($callback) {
        Mail::assertQueued($mailableClass, $callback);

        return;
    }

    Mail::assertQueued($mailableClass);
}

/*
|--------------------------------------------------------------------------
| TenantSettings Builders
|--------------------------------------------------------------------------
| Construct TenantSettings (and its sub-DTOs) with sensible defaults so
| tests only override the fields they care about.
*/

/** @param array{name?: string, email?: ?string, phone?: ?string, address?: ?string, website?: ?string, photo?: ?string, logo?: ?string, tagline?: ?string} $overrides */
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

/**
 * @param array{brandColorPrimary?: string, storefrontTheme?: string, businessTagline?: ?string, aboutUsText?: ?string, heroImage?: ?string, heroStyle?: string, heroTagline?: ?string, heroPrimaryCtaText?: string, heroSecondaryCtaText?: string, allergyDisclaimer?: ?string, cateringHeroImage?: ?string, loyaltyHeroImage?: ?string, giftCardsHeroImage?: ?string} $overrides
 */
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

/**
 * @param array{leadTimeHours?: int, deliveryEnabled?: bool, freeDeliveryMinimum?: string, minimumPickupOrderAmount?: string, minimumDeliveryOrderAmount?: string, deliveryFeeTiers?: array<int, array<string, mixed>>, defaultDailyCapacity?: int, modificationWindowMinutes?: int, pickupSlotsEnabled?: bool, pickupSlotIntervalMinutes?: int, pickupSlotMaxPerWindow?: int, sitewideSaleEnabled?: bool, sitewideSalePercent?: int, sitewideSaleLabel?: string} $overrides
 */
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

/**
 * @param array{birthdayProgramEnabled?: bool, birthdayCouponEnabled?: bool, birthdayDiscountPercentage?: int, birthdayCouponValidDays?: int, reviewRequestsEnabled?: bool, reviewRequestDelayHours?: int, repeatRemindersEnabled?: bool, repeatReminderDays?: int, announcementEnabled?: bool, announcementText?: string, announcementType?: string, emailOrderPlacedEnabled?: bool, emailOrderConfirmedEnabled?: bool, emailOrderBakingEnabled?: bool, emailOrderReadyEnabled?: bool, emailOrderDeliveredEnabled?: bool, emailOrderCancelledEnabled?: bool, emailOrderMessageEnabled?: bool, emailProductAvailableEnabled?: bool, customerReferralProgramEnabled?: bool, customerReferralDiscountDollars?: int, abandonedCartRecoveryEnabled?: bool, abandonedCartRecoveryHours?: int, abandonedCartRecoveryCouponDollars?: int, lowReviewAlertThreshold?: int} $overrides
 */
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
        'emailOrderPlacedEnabled' => true,
        'emailOrderConfirmedEnabled' => true,
        'emailOrderBakingEnabled' => true,
        'emailOrderReadyEnabled' => true,
        'emailOrderDeliveredEnabled' => true,
        'emailOrderCancelledEnabled' => true,
        'emailOrderMessageEnabled' => true,
        'emailProductAvailableEnabled' => true,
    ], $overrides));
}

/** @param array{showOnStorefront?: bool, cancellation?: string, deposit?: string, refund?: string, pickup?: string, additionalTerms?: string} $overrides */
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

/**
 * @param array{socialMediaLinks?: array<string, string>, operatingHours?: array<string, mixed>, faqItems?: array<int, array<string, mixed>>, sections?: array<string, array<string, mixed>>} $overrides
 */
function makeHomepageSettings(array $overrides = []): App\DataTransferObjects\Settings\HomepageSettings
{
    return new App\DataTransferObjects\Settings\HomepageSettings(...array_merge([
        'socialMediaLinks' => [],
        'operatingHours' => [],
        'faqItems' => [],
        'sections' => [],
    ], $overrides));
}

/** @param array{enabled?: bool, minimumGuests?: string, leadTimeDays?: string, eventTypes?: array<int, string>, depositPercent?: int} $overrides */
function makeCateringSettings(array $overrides = []): App\DataTransferObjects\Settings\CateringSettings
{
    return new App\DataTransferObjects\Settings\CateringSettings(...array_merge([
        'enabled' => false,
        'minimumGuests' => '10',
        'leadTimeDays' => '14',
        'eventTypes' => ['Wedding', 'Corporate Event'],
    ], $overrides));
}

/**
 * @param array{enabled?: bool, pointsPerDollar?: int, programName?: string, tiersEnabled?: bool, tierSilverThreshold?: int, tierGoldThreshold?: int, tierPlatinumThreshold?: int, tierPerksEnabled?: bool, tierSilverMultiplier?: float, tierSilverFreeDelivery?: bool, tierGoldMultiplier?: float, tierGoldFreeDelivery?: bool, tierPlatinumMultiplier?: float, tierPlatinumFreeDelivery?: bool} $overrides
 */
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
    ?App\DataTransferObjects\Settings\InventorySettings $inventory = null,
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
        inventory: $inventory ?? new App\DataTransferObjects\Settings\InventorySettings(
            lowStockAlertsEnabled: false,
        ),
    );
}
