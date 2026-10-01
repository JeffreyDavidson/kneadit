<?php

namespace App\Console\Commands\Platform;

use App\Events\Platform\WeeklyDigestRequested;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Notifications\ScheduledNotificationRunTracker;
use App\Services\Reporting\WeeklyDigestDataCollector;
use App\Services\Scheduling\LocalSendSchedule;
use App\Services\Scheduling\LocalSendWindow;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettingsRegistry;
use App\Services\Tenants\TenancyManager;
use Carbon\WeekDay;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('digest:weekly {--force : Run now, ignoring the bakery-local send day and hour}')]
#[Description('Send weekly digest email to bakery owners')]
class SendWeeklyDigestCommand extends Command
{
    /** Each bakery gets its digest when its own clock reads this hour on this weekday. */
    private const int SEND_HOUR = 8;

    private const WeekDay SEND_DAY = WeekDay::Monday;

    public function handle(
        TenancyManager $tenancyManager,
        ScheduledNotificationRunTracker $runTracker,
        LocalSendWindow $window,
    ): int {
        $schedule = new LocalSendSchedule('digest:weekly', self::SEND_HOUR, self::SEND_DAY);
        $force = $this->option('force') === true;
        $tenants = Tenant::query()->cursor();
        $failures = 0;

        foreach ($tenants as $tenant) {
            try {
                $tenancyManager->withinTenant($tenant, function () use ($tenant, $runTracker, $window, $schedule, $force): void {
                    $window->run(
                        $schedule,
                        resolve(TenantSettingsRegistry::class)->all(),
                        $force,
                        fn () => $this->sendDigest($tenant, $runTracker),
                    );
                });
            } catch (\Throwable $e) {
                $this->error("Failed for {$tenant->id}: {$e->getMessage()}");
                Log::warning('Weekly digest processing failed', ['tenant' => $tenant->id, 'error' => $e->getMessage()]);
                $failures++;
            }
        }

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function sendDigest(Tenant $tenant, ScheduledNotificationRunTracker $runTracker): void
    {
        if (resolve(SettingsManager::class)->get('weekly_digest_enabled', '1') !== '1') {
            $this->info("Skipping {$tenant->id} — digest disabled");

            return;
        }

        $users = User::query()->owners()->get();

        if ($users->isEmpty()) {
            $users = User::query()->limit(1)->get();
        }

        $week = now()->startOfWeek()->toDateString();
        $users = $users->filter(
            fn (User $user): bool => $runTracker->claim("weekly-digest:{$week}:{$user->id}"),
        )->values();

        if ($users->isEmpty()) {
            return;
        }

        $data = resolve(WeeklyDigestDataCollector::class)->collect();

        foreach ($users as $user) {
            event(new WeeklyDigestRequested($user, $data));
        }

        $this->info("Sent digest for {$tenant->id} to {$users->count()} user(s)");
    }
}
