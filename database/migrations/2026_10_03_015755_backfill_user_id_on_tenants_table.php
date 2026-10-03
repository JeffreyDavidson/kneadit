<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant creation never wrote tenants.user_id, so User::tenants() was empty
 * for every real signup. Link existing tenants to the central user whose
 * email matches the tenant's contact email, ignoring case. Tenants with no
 * matching user (e.g. the owner changed their email) stay unlinked.
 *
 * Raw queries (not the Tenant model) so this runs on the default connection,
 * matching the free_forever_grants backfill.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('tenants')
            ->whereNull('user_id')
            ->get(['id', 'email'])
            ->each(function (stdClass $tenant): void {
                $userId = DB::table('users')
                    ->whereRaw('LOWER(email) = LOWER(?)', [$tenant->email])
                    ->value('id');

                if ($userId === null) {
                    return;
                }

                DB::table('tenants')->where('id', $tenant->id)->update(['user_id' => $userId]);
            });
    }
};
