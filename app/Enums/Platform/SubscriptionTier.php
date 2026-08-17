<?php

namespace App\Enums\Platform;

use App\Models\Staff\User;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Facades\Config;

enum SubscriptionTier: string implements HasColor, HasLabel
{
    case Starter = 'starter';
    case Growth = 'growth';
    case Pro = 'pro';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Starter => 'warning',
            self::Growth => 'info',
            self::Pro => 'success',
        };
    }

    public function level(): int
    {
        return match ($this) {
            self::Starter => 1,
            self::Growth => 2,
            self::Pro => 3,
        };
    }

    /**
     * Check if this tier meets or exceeds the required tier.
     */
    public function meetsRequirement(self $required): bool
    {
        return $this->level() >= $required->level();
    }

    public function priceInDollars(): int
    {
        return intdiv(Config::integer("kneadit.plans.{$this->value}.price_monthly"), 100);
    }

    public function labelWithPrice(): string
    {
        return "{$this->getLabel()} (\${$this->priceInDollars()}/mo)";
    }

    /** @return array<string, int> */
    public static function priceMap(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $tier) => [$tier->value => $tier->priceInDollars()])
            ->all();
    }

    public static function fromPriceId(string $priceId): ?self
    {
        $prices = Config::array('kneadit.stripe_prices', []);

        $plan = array_search($priceId, $prices, true);

        return is_string($plan) ? self::tryFrom($plan) : null;
    }

    public static function resolve(User $user): ?self
    {
        // Platform-admin-granted comp accounts get the highest tier without
        // a Stripe subscription. The free_forever flag lives on the tenant
        // because plan-level access is per-tenant — an owner with multiple
        // tenants is free-forever only on the ones explicitly flagged.
        if ($user->tenants()->where('free_forever', true)->exists()) {
            return self::Pro;
        }

        $priceId = $user->subscription('default')?->stripe_price;

        return $priceId ? self::fromPriceId($priceId) : null;
    }
}
