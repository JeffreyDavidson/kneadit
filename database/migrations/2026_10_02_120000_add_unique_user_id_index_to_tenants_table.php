<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->refuseWhenAUserOwnsSeveralBakeries();
        $this->linkUnownedBakeriesToTheirOnlyMatchingUser();

        // A unique index on a nullable column: SQLite compiles this to CREATE UNIQUE INDEX,
        // so the table is not rebuilt and its existing indexes and foreign key survive.
        // Tenants without an owner (demo and platform tenants) all stay NULL, which is allowed.
        Schema::table('tenants', function (Blueprint $table): void {
            $table->unique('user_id');
        });
    }

    private function refuseWhenAUserOwnsSeveralBakeries(): void
    {
        $owners = DB::table('tenants')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('count(*) > 1')
            ->pluck('user_id');

        if ($owners->isEmpty()) {
            return;
        }

        $details = [];

        foreach ($owners as $userId) {
            $details[] = [
                'user_id' => $userId,
                'tenant_ids' => DB::table('tenants')->where('user_id', $userId)->pluck('id')->all(),
            ];
        }

        Log::warning('Users own more than one bakery, so the one-bakery-per-account index was not added. Resolve them by hand.', ['owners' => $details]);

        throw new RuntimeException('Cannot add the unique owner index: some users own more than one bakery. See the log for the user and tenant ids, resolve them by hand, then run the migration again.');
    }

    /**
     * Earlier onboarding never wrote the owner. Link a tenant to a user only when
     * exactly one user has the tenant's email and that user does not already own a
     * bakery; anything else is left unowned and logged.
     */
    private function linkUnownedBakeriesToTheirOnlyMatchingUser(): void
    {
        $owned = DB::table('tenants')->whereNotNull('user_id')->pluck('user_id')->all();
        $skipped = [];

        foreach (DB::table('tenants')->whereNull('user_id')->get(['id', 'email']) as $tenant) {
            $userIds = DB::table('users')
                ->whereRaw('lower(email) = ?', [mb_strtolower(is_string($tenant->email) ? $tenant->email : '')])
                ->pluck('id');

            if ($userIds->count() !== 1 || in_array($userIds->first(), $owned, true)) {
                $skipped[] = $tenant->id;

                continue;
            }

            DB::table('tenants')->where('id', $tenant->id)->update(['user_id' => $userIds->first()]);
            $owned[] = $userIds->first();
        }

        if ($skipped === []) {
            return;
        }

        Log::warning('Bakeries without an owner were left unlinked because no single user matched their email.', ['tenant_ids' => $skipped]);
    }
};
