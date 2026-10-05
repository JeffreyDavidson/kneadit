<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tenants', 'subdomain')) {
            // A plain nullable column with no constraint: SQLite adds it in place, so the
            // table is not rebuilt and its existing CHECK constraints and indexes survive.
            Schema::table('tenants', function (Blueprint $table): void {
                $table->string('subdomain')->nullable()->after('id');
            });
        }

        $this->backfill();
    }

    /**
     * Each bakery's subdomain is its one dot-less `domains` row (the label that resolves
     * `{label}.{tenant domain}`), or its id when it does not have exactly one.
     */
    private function backfill(): void
    {
        foreach (DB::table('tenants')->whereNull('subdomain')->pluck('id') as $id) {
            $labels = DB::table('domains')
                ->where('tenant_id', $id)
                ->where('domain', 'not like', '%.%')
                ->pluck('domain');

            DB::table('tenants')->where('id', $id)->update([
                'subdomain' => $labels->count() === 1 ? $labels->first() : $id,
            ]);
        }
    }
};
