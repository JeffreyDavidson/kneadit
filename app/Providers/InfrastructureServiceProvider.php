<?php

namespace App\Providers;

use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Support\Csp\CspNonce;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use RuntimeException;
use Stripe\StripeClient;

class InfrastructureServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->app->scoped(CspNonce::class);
        $this->app->bind(StripeClient::class, fn () => new StripeClient(
            Config::string('cashier.secret', ''),
        ));
    }

    public function boot(): void
    {
        Queue::createPayloadUsing(function (): array {
            $tenant = tenancy()->initialized ? tenancy()->tenant : null;

            if (! $tenant instanceof Tenant) {
                return [];
            }

            return [
                'tenant_id' => $tenant->getTenantKey(),
                'is_demo' => (bool) $tenant->is_demo,
            ];
        });

        Cashier::useCustomerModel(User::class);
        Model::preventLazyLoading(! app()->isProduction());

        CacheRepository::handleUnserializableClassUsing(function (string $key, ?string $class): void {
            $message = sprintf(
                'Cache returned __PHP_Incomplete_Class for key [%s] (original class: %s). Likely a model/collection cached against the project rule (see .ai/skills/laravel-best-practices/rules/caching.md).',
                $key,
                $class ?? 'unknown',
            );

            Log::error($message);

            if (! app()->isProduction()) {
                throw new RuntimeException($message);
            }
        });
    }
}
