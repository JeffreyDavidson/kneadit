<?php

namespace App\Actions\Tenants;

use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Settings\SettingsManager;
use Illuminate\Support\Facades\DB;

class ProvisionTenantOwner
{
    public function __construct(
        private SettingsManager $settingsManager,
    ) {}

    public function __invoke(
        Tenant $tenant,
        User $user,
        string $storeName,
        bool $useKneadItStorefront,
        ?string $externalWebsite,
    ): void {
        $tenant->run(function () use ($user, $storeName, $useKneadItStorefront, $externalWebsite): void {
            // A raw insert preserves the central password hash and verified timestamp;
            // the tenant User model's cast/fillable boundary would alter or drop them.
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

            $this->settingsManager->setMany($settings);
        });
    }
}
