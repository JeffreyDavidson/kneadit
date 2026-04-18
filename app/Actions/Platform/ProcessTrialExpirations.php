<?php

namespace App\Actions\Platform;

use App\Events\Platform\TrialExpired;
use App\Events\Platform\TrialReminding;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Log;

class ProcessTrialExpirations
{
    /** @var array<int, int> */
    private const array REMINDER_DAYS = [7, 3, 1];

    /** @return array{reminders: int, pausings: int, failures: int} */
    public function __invoke(): array
    {
        $reminders = 0;
        $failures = 0;

        foreach (self::REMINDER_DAYS as $days) {
            [$sent, $errors] = $this->sendReminders($days);
            $reminders += $sent;
            $failures += $errors;
        }

        $pausings = $this->pauseExpired();

        return [
            'reminders' => $reminders,
            'pausings' => $pausings,
            'failures' => $failures,
        ];
    }

    /** @return array{0: int, 1: int} [sent, failures] */
    private function sendReminders(int $daysLeft): array
    {
        $targetDate = now()->addDays($daysLeft)->startOfDay()->toDateString();
        $cacheKey = "trial_reminder_{$daysLeft}d";

        $sent = 0;
        $failures = 0;

        $tenants = Tenant::query()
            ->whereDate('trial_ends_at', $targetDate)
            ->where('is_active', true)
            ->cursor();

        /** @var Tenant $tenant */
        foreach ($tenants as $tenant) {
            $sentKey = "sent_{$cacheKey}_{$tenant->id}";
            if (cache()->has($sentKey)) {
                continue;
            }

            $user = User::query()->where('email', $tenant->email)->first();
            if (! $user || $user->subscribed('default')) {
                continue;
            }

            try {
                $storeName = $tenant->store_name ?: $tenant->name;
                TrialReminding::dispatch($user, $storeName, $daysLeft);
                cache()->put($sentKey, true, now()->addDays(30));
                $sent++;
            } catch (\Exception $e) {
                Log::error('Trial reminder dispatch failed', [
                    'email' => $user->email,
                    'tenant' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);
                $failures++;
            }
        }

        return [$sent, $failures];
    }

    private function pauseExpired(): int
    {
        $pausings = 0;

        $expiredTenants = Tenant::query()
            ->where('trial_ends_at', '<', now())
            ->where('is_active', true)
            ->where('storefront_enabled', true)
            ->cursor();

        /** @var Tenant $tenant */
        foreach ($expiredTenants as $tenant) {
            $user = User::query()->where('email', $tenant->email)->first();

            if ($user && $user->subscribed('default')) {
                continue;
            }

            $tenant->update(['storefront_enabled' => false]);
            $pausings++;

            if ($user) {
                try {
                    TrialExpired::dispatch($user, $tenant->id);
                } catch (\Exception $e) {
                    Log::error('Trial expiration email dispatch failed', [
                        'tenant' => $tenant->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Trial expired — storefront paused', ['tenant' => $tenant->id]);
        }

        return $pausings;
    }
}
