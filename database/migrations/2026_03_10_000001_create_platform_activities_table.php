<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_activities', function (Blueprint $table) {
            $table->id();
            $table->string('event'); // tenant_created, tenant_deactivated, plan_changed, storefront_toggled, trial_expired
            $table->string('tenant_id')->nullable();
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_activities');
    }
};
