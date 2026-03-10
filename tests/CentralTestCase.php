<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class CentralTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Point central connection to the same test SQLite in-memory DB
        config(['database.connections.central' => config('database.connections.sqlite')]);
        config(['tenancy.central_domains' => []]);

        // Share the same PDO instance so central and sqlite use the same in-memory DB
        $pdo = DB::connection('sqlite')->getPdo();
        DB::connection('central')->setPdo($pdo);
        DB::connection('central')->setReadPdo($pdo);

        // Run tenant migrations on the same DB
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }

        // Create central-only tables
        $this->createCentralTables();
    }

    protected function createCentralTables(): void
    {
        // Create tenants table if not present (central migration)
        if (! Schema::connection('central')->hasTable('tenants')) {
            Schema::connection('central')->create('tenants', function ($table) {
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

        // Create domains table if not present (tenancy requirement)
        if (! Schema::connection('central')->hasTable('domains')) {
            Schema::connection('central')->create('domains', function ($table) {
                $table->increments('id');
                $table->string('domain', 255)->unique();
                $table->string('tenant_id');
                $table->timestamps();
                $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
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
                $table->text('body');
                $table->string('sender_type')->default('admin');
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
                $table->integer('count')->default(1);
                $table->date('date');
                $table->timestamps();
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
        ];

        foreach ($tables as $name => $callback) {
            if (! Schema::connection('central')->hasTable($name)) {
                Schema::connection('central')->create($name, function ($table) use ($callback) {
                    $callback($table);
                });
            }
        }
    }
}
