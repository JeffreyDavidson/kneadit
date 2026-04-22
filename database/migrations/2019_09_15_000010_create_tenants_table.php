<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            // Tenant info
            $table->string('name'); // Owner name
            $table->string('email');
            $table->string('plan')->default('starter'); // starter, growth, pro
            $table->timestamp('trial_ends_at')->nullable();

            // Store branding
            $table->string('store_name')->nullable();
            $table->string('store_logo')->nullable();
            $table->string('brand_color_primary')->default('#d4920c');
            $table->string('brand_color_secondary')->default('#1c1410');
            $table->boolean('storefront_enabled')->default(true);
            $table->string('external_website')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->json('data')->nullable();
        });
    }
};
