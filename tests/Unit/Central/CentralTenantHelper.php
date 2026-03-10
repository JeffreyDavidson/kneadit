<?php

namespace Tests\Unit\Central;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CentralTenantHelper
{
    protected function ensureCentralTenantsTable(): void
    {
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
    }

    protected function createTenant(array $attrs): void
    {
        $defaults = [
            'is_active' => true,
            'data' => '{}',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::connection('central')->table('tenants')->insert(array_merge($defaults, $attrs));
    }
}
