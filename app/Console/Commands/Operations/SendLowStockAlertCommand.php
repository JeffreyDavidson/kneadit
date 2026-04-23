<?php

namespace App\Console\Commands\Operations;

use App\Mail\Operations\LowStockAlertMail;
use App\Models\Inventory\Ingredient;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Mail;

#[Signature('inventory:send-low-stock-alert')]
#[Description('Email each baker a daily digest of ingredients at or below their low-stock threshold')]
class SendLowStockAlertCommand extends Command
{
    public function handle(TenancyManager $tenancyManager): int
    {
        $failures = $tenancyManager->forEachTenant(
            function (Tenant $tenant, TenantSettings $settings): void {
                if (! $settings->inventory->lowStockAlertsEnabled) {
                    return;
                }

                $recipient = $settings->store->email ?? null;
                if (! $recipient) {
                    $this->warn("Skipping {$tenant->id} — no store email configured");

                    return;
                }

                $ingredients = Ingredient::query()
                    ->where(function (QueryBuilder $q): void {
                        $q->where('current_stock', '<=', 0)
                            ->orWhereColumn('current_stock', '<=', 'low_stock_threshold');
                    })
                    ->orderBy('current_stock')
                    ->get();

                if ($ingredients->isEmpty()) {
                    return;
                }

                Mail::to($recipient)->queue(new LowStockAlertMail($ingredients));
                $this->info("Queued low-stock alert for {$tenant->id} ({$ingredients->count()} ingredients)");
            },
        );

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
