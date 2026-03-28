<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('admin_audit_logs')) {
            Schema::table('admin_audit_logs', function (Blueprint $table) {
                $table->index('created_at');
            });
        }

        if (Schema::hasTable('platform_activities')) {
            Schema::table('platform_activities', function (Blueprint $table) {
                $table->index('created_at');
                $table->index('tenant_id');
            });
        }

        if (Schema::hasTable('email_campaigns')) {
            Schema::table('email_campaigns', function (Blueprint $table) {
                $table->index('status');
            });
        }
    }
};
