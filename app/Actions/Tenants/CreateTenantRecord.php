<?php

namespace App\Actions\Tenants;

use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CreateTenantRecord
{
    public function __invoke(
        User $user,
        string $storeName,
        string $subdomain,
        bool $useKneadItStorefront,
        ?string $externalWebsite,
    ): Tenant {
        return DB::transaction(function () use ($user, $storeName, $subdomain, $useKneadItStorefront, $externalWebsite): Tenant {
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
    }
}
