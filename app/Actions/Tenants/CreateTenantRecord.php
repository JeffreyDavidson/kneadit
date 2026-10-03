<?php

namespace App\Actions\Tenants;

use App\Enums\Platform\SubscriptionTier;
use App\Exceptions\Platform\UserAlreadyHasBakeryException;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Database\Models\Domain;

class CreateTenantRecord
{
    /**
     * @throws UserAlreadyHasBakeryException when the user already owns a bakery
     */
    public function __invoke(
        User $user,
        string $storeName,
        string $subdomain,
        bool $useKneadItStorefront,
        ?string $externalWebsite,
    ): Tenant {
        try {
            return DB::transaction(function () use ($user, $storeName, $subdomain, $useKneadItStorefront, $externalWebsite): Tenant {
                throw_if($user->tenants()->exists(), UserAlreadyHasBakeryException::class);

                $tenant = Tenant::query()->create([
                    'id' => $subdomain,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'plan' => SubscriptionTier::Starter->value,
                    'trial_ends_at' => now()->addDays(Config::integer('kneadit.trial_days', 30)),
                    'store_name' => $storeName,
                    'storefront_enabled' => $useKneadItStorefront,
                    'external_website' => $useKneadItStorefront ? null : $externalWebsite,
                    'is_active' => true,
                ]);

                Domain::query()->create([
                    'domain' => $subdomain,
                    'tenant_id' => $tenant->id,
                ]);

                return $tenant;
            });
        } catch (UniqueConstraintViolationException $exception) {
            // A second submission can pass the check above before the first one commits.
            // The unique index on tenants.user_id then rejects it; any other unique
            // violation (such as a taken subdomain) is not about the owner.
            throw_if(! $user->tenants()->exists(), $exception);

            throw new UserAlreadyHasBakeryException;
        }
    }
}
