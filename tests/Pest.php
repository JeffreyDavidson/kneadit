<?php

use App\Enums\Platform\SubscriptionTier;
use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature', 'Integration', 'Unit', 'Browser');

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
