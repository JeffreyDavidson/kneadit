<?php

namespace App\Console\Commands\Operations;

use App\Mail\Operations\LowStockAlertMail;
use App\Models\Inventory\Ingredient;
use App\Models\Platform\Tenant;
use App\Services\Notifications\ScheduledNotificationRunTracker;
use App\Services\Scheduling\LocalSendSchedule;
use App\Services\Scheduling\LocalSendWindow;
use App\Services\Settings\TenantSettings;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('inventory:send-low-stock-alert {--force : Run now, ignoring the bakery-local send hour}')]
#[Description('Email each baker a daily digest of ingredients at or below their low-stock threshold')]
class SendLowStockAlertCommand extends Command
{
    /** Each bakery is alerted when its own clock reads this hour. */
    private const int SEND_HOUR = 7;

    public function handle(
        TenancyManager $tenancyManager,
        ScheduledNotificationRunTracker $runTracker,
        LocalSendWindow $window,
    ): int {
        $schedule = new LocalSendSchedule('inventory:send-low-stock-alert', self::SEND_HOUR);
        $force = $this->option('force') === true;

        $failures = $tenancyManager->forEachTenant(
            fn (Tenant $tenant, TenantSettings $settings) => $window->run(
                $schedule,
                $settings,
                $force,
                fn () => $this->sendAlert($tenant, $settings, $runTracker),
            ),
        );

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function sendAlert(Tenant $tenant, TenantSettings $settings, ScheduledNotificationRunTracker $runTracker): void
    {
        if (! $settings->inventory->lowStockAlertsEnabled) {
            return;
        }

        $recipient = $settings->store->email ?? null;
        if (! $recipient) {
            $this->warn("Skipping {$tenant->id} — no store email configured");

            return;
        }

        $ingredients = Ingredient::query()
            ->lowStock()
            ->orderBy('current_stock')
            ->get();

        if ($ingredients->isEmpty()) {
            return;
        }

        if (! $runTracker->claim('low-stock:'.now()->toDateString())) {
            return;
        }

        Mail::to($recipient)->queue(new LowStockAlertMail($ingredients));
        $this->info("Queued low-stock alert for {$tenant->id} ({$ingredients->count()} ingredients)");
    }
}
