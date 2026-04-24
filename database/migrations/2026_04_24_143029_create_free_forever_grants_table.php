<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Audit ledger for free_forever grants. The platform:audit-free-forever
 * command diffs tenants.free_forever=true against active grants here —
 * any tenant flagged free without an active grant alerts the platform
 * admin (someone bypassed the admin UI, e.g. direct DB write).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('free_forever_grants', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('granted_by_user_id')->nullable()->comment('Platform admin who granted (nullable if backfilled)');
            $table->timestamp('granted_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'revoked_at']);
        });

        // Backfill: any tenant already marked free_forever=true at deploy
        // time gets a grant attributed to no specific user. Without this
        // the audit command would alert on every existing free-forever
        // tenant on first run.
        // Use raw query (not the Tenant model) so this runs on the default
        // connection during migration rather than routing to Tenant's
        // configured connection, which may not be aligned yet during
        // test bootstrap (DB::purge happens after migrate:fresh).
        $existing = DB::table('tenants')->where('free_forever', true)->pluck('id');
        foreach ($existing as $tenantId) {
            DB::table('free_forever_grants')->insert([
                'tenant_id' => $tenantId,
                'granted_by_user_id' => null,
                'granted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
