<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('tenants', 'paused_at')) {
            return;
        }

        // A plain nullable column with no constraint: SQLite adds it in place, so the
        // table is not rebuilt and its existing CHECK constraints and indexes survive.
        Schema::table('tenants', function (Blueprint $table): void {
            $table->timestamp('paused_at')->nullable()->after('storefront_enabled');
        });
    }
};
