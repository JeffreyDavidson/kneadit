<?php

namespace App\Actions\Tenants;

use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Settings\SettingsManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CreateTenant
{
    public function __invoke(
        User $user,
        string $storeName,
        string $subdomain,
        bool $useKneadItStorefront,
        ?string $externalWebsite = null,
    ): Tenant {
        $tenant = DB::transaction(function () use ($user, $storeName, $subdomain, $useKneadItStorefront, $externalWebsite) {
            $tenant = Tenant::query()->create([
                'id' => $subdomain,
                'name' => $user->name,
                'email' => $user->email,
                'plan' => SubscriptionTier::Starter->value,
                'trial_ends_at' => now()->addDays(Config::integer('kneadit.trial_days', 30)),
                'store_name' => $storeName,
                'storefront_enabled' => $useKneadItStorefront,
                'external_website' => $useKneadItStorefront ? null : $externalWebsite,
                'is_active' => true,
            ]);

            $tenant->domains()->create([
                'domain' => $subdomain,
            ]);

            return $tenant;
        });

        // The central tenant + domain rows are committed above. The tenant DB
        // is created+migrated synchronously by Stancl's TenantCreated job
        // pipeline. Now we seed the tenant DB. If THAT fails — and SQLite is
        // a separate connection from central, so it's outside the central
        // transaction — we'd otherwise leave an orphan central tenant row
        // (the situation tenants:doctor was built to recover from).
        //
        // Catch + cascade-delete the central tenant on failure so the caller
        // sees an all-or-nothing outcome. $tenant->delete() cascades to the
        // domain row (FK onDelete cascade) and fires Stancl's TenantDeleted
        // event which runs DeleteDatabase to remove the SQLite file.
        try {
            $tenant->run(function () use ($user, $storeName, $useKneadItStorefront, $externalWebsite) {
                // Raw insert (not User::create): the model's `password => hashed`
                // cast would re-hash the already-hashed central password and
                // break login, and `email_verified_at` isn't in #[Fillable] so
                // create() would silently drop it and leave the owner unverified.
                DB::table('users')->insert([
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $user->password,
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $settings = [
                    'store_name' => $storeName,
                    'store_email' => $user->email,
                    'storefront_enabled' => $useKneadItStorefront ? '1' : '0',
                ];

                if (! $useKneadItStorefront && $externalWebsite) {
                    $settings['external_website'] = $externalWebsite;
                }

                resolve(SettingsManager::class)->setMany($settings);
            });
        } catch (\Throwable $e) {
            $tenant->delete();
            throw $e;
        }

        return $tenant;
    }
}
