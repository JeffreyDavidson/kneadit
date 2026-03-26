<?php

namespace App\Actions\Tenants;

use App\Enums\SubscriptionTier;
use App\Models\Tenant;
use App\Models\User;
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
                'trial_ends_at' => now()->addDays(config('saas.trial_days', 30)),
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

        $tenant->run(function () use ($user, $storeName, $useKneadItStorefront, $externalWebsite) {
            DB::table('users')->insert([
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            settings(['store_name' => $storeName]);
            settings(['store_email' => $user->email]);
            settings(['storefront_enabled' => $useKneadItStorefront ? '1' : '0']);

            if (! $useKneadItStorefront && $externalWebsite) {
                settings(['external_website' => $externalWebsite]);
            }
        });

        return $tenant;
    }
}
