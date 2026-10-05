<?php

declare(strict_types=1);

namespace App\Queries\Platform;

use App\DataTransferObjects\Platform\PlatformRevenueMetrics;
use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Cashier\Subscription;

/**
 * Platform revenue figures for the central dashboard, taken from the owners'
 * Cashier "default" subscriptions. The definitions are in docs/architecture.md.
 */
final class PlatformRevenueMetricsQuery
{
    private const int CHURN_WINDOW_DAYS = 30;

    public function get(): PlatformRevenueMetrics
    {
        $paying = $this->payingSubscriptions();
        $mrr = $this->monthlyRevenue($paying);
        $payingCount = $paying->count();
        $churnedCount = $this->churnedCount();
        $trialedCount = $this->finishedTrials()->count();
        $convertedCount = $this->finishedTrials()
            ->whereIn('user_id', $this->paidSubscriptions($this->billableBakeries())->pluck('user_id')->all())
            ->count();

        return new PlatformRevenueMetrics(
            mrr: $mrr,
            payingCount: $payingCount,
            arpu: $payingCount > 0 ? $mrr / $payingCount : 0.0,
            churnedCount: $churnedCount,
            churnRate: $this->percentage($churnedCount, $churnedCount + $payingCount),
            trialedCount: $trialedCount,
            convertedCount: $convertedCount,
            trialConversion: $this->percentage($convertedCount, $trialedCount),
        );
    }

    /**
     * MRR, in dollars, at each of the given moments, counting only the
     * subscriptions that pay today and had started by then.
     *
     * @param  list<Carbon>  $moments
     * @return list<float>
     */
    public function monthlyRevenueAt(array $moments): array
    {
        $paying = $this->payingSubscriptions();

        return array_map(
            fn (Carbon $moment): float => (float) $this->monthlyRevenue(
                $paying->filter(fn (Subscription $subscription): bool => $subscription->created_at <= $moment),
            ),
            $moments,
        );
    }

    /** @return Collection<int, Subscription> */
    private function payingSubscriptions(): Collection
    {
        return $this->paidSubscriptions($this->billableBakeries()->whereNull('paused_at'));
    }

    /**
     * Bakeries that can pay: not a demo and not comped.
     *
     * @return Builder<Tenant>
     */
    private function billableBakeries(): Builder
    {
        return Tenant::query()
            ->whereNotNull('user_id')
            ->where('is_demo', false)
            ->where('free_forever', false);
    }

    /**
     * Subscriptions that bring in money: valid, past any Stripe trial, priced
     * by a known plan, and owned by one of the given bakeries' owners (a bakery
     * has one owner, and tenants.user_id is unique).
     *
     * @param  Builder<Tenant>  $bakeries
     * @return Collection<int, Subscription>
     */
    private function paidSubscriptions(Builder $bakeries): Collection
    {
        return Subscription::query()
            ->where('type', 'default')
            ->whereIn('user_id', $bakeries->select('user_id'))
            ->get()
            ->filter(fn (Subscription $subscription): bool => $subscription->valid()
                && ! $subscription->onTrial()
                && $this->tier($subscription) instanceof SubscriptionTier)
            ->values();
    }

    /** @param Collection<int, Subscription> $subscriptions */
    private function monthlyRevenue(Collection $subscriptions): int
    {
        return $subscriptions->sum(fn (Subscription $subscription): int => $this->tier($subscription)?->priceInDollars() ?? 0);
    }

    private function tier(Subscription $subscription): ?SubscriptionTier
    {
        return $subscription->stripe_price === null
            ? null
            : SubscriptionTier::fromPriceId($subscription->stripe_price);
    }

    /**
     * Bakeries lost in the last 30 days: the owner's subscription ended and
     * they pay nothing now, or the bakery was paused after its trial.
     */
    private function churnedCount(): int
    {
        $since = now()->subDays(self::CHURN_WINDOW_DAYS);
        $payingOwnerIds = $this->paidSubscriptions($this->billableBakeries())->pluck('user_id')->all();

        $endedOwnerIds = Subscription::query()
            ->where('type', 'default')
            ->ended()
            ->whereBetween('ends_at', [$since, now()])
            ->whereNotIn('user_id', $payingOwnerIds)
            ->pluck('user_id')
            ->all();

        return $this->billableBakeries()
            ->where(fn (Builder $query): Builder => $query
                ->whereIn('user_id', $endedOwnerIds)
                ->orWhere(fn (Builder $paused): Builder => $paused
                    ->whereNotNull('trial_ends_at')
                    ->whereBetween('paused_at', [$since, now()])
                    ->whereColumn('paused_at', '>=', 'trial_ends_at')))
            ->count();
    }

    /** @return Builder<Tenant> */
    private function finishedTrials(): Builder
    {
        return $this->billableBakeries()->where('trial_ends_at', '<=', now());
    }

    private function percentage(int $part, int $whole): float
    {
        return $whole > 0 ? round(($part / $whole) * 100, 1) : 0.0;
    }
}
