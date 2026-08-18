<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->unsignedInteger('onboarding_products_count')->default(0);
            $table->unsignedInteger('onboarding_categories_count')->default(0);
            $table->unsignedInteger('onboarding_orders_count')->default(0);
            $table->timestamp('onboarding_metrics_synced_at')->nullable();
        });
    }
};
