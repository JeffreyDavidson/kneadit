<?php

namespace App\Console\Commands\Platform;

use App\Actions\Stripe\CreateStripePromotionCode;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Stripe\Exception\ApiErrorException;

#[Signature('platform:create-promo
    {--tenant= : Tenant ID stamped into the coupon metadata for reconciliation}
    {--percent= : Percent off, 1..100}
    {--amount= : Amount off in cents (alternative to --percent)}
    {--duration=once : once | repeating | forever}
    {--months= : Required when duration=repeating}
    {--code= : The redeemable string customers type at checkout (auto-generated if omitted)}
    {--max= : Max redemptions (default 1)}
    {--expires-in= : Expiry in days from now}
    {--name= : Human-readable coupon name (shown in Stripe dashboard)}')]
#[Description('Create a Stripe coupon + promotion code in one call and print the redeemable code')]
class CreateStripePromoCommand extends Command
{
    public function handle(CreateStripePromotionCode $action): int
    {
        try {
            $promo = $action(
                percentOff: $this->option('percent') !== null ? (int) $this->option('percent') : null,
                amountOffCents: $this->option('amount') !== null ? (int) $this->option('amount') : null,
                duration: $this->option('duration') ?: 'once',
                durationInMonths: $this->option('months') !== null ? (int) $this->option('months') : null,
                code: $this->option('code') ?: null,
                maxRedemptions: $this->option('max') !== null ? (int) $this->option('max') : 1,
                expiresInDays: $this->option('expires-in') !== null ? (int) $this->option('expires-in') : null,
                tenantId: $this->option('tenant') ?: null,
                name: $this->option('name') ?: null,
            );
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        } catch (ApiErrorException $e) {
            $this->error('Stripe rejected the request: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("✅ Promotion code created: {$promo->code}");
        $this->line("Coupon ID: {$promo->couponId}");
        $this->line("Promotion Code ID: {$promo->promotionCodeId}");
        $this->newLine();
        $this->line('Hand this code to the customer — they type it at Stripe Checkout.');

        return self::SUCCESS;
    }
}
