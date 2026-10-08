<?php

namespace App\Actions\Platform;

use App\Models\Staff\User;
use App\Queries\Platform\OwnerSubscriptionsQuery;
use App\Services\Platform\TrialExpirationNotifier;
use App\Services\Platform\TrialExpirationReader;
use Illuminate\Support\Facades\Log;

class ProcessTrialExpirations
{
    /** @var array<int, int> */
    private const array REMINDER_DAYS = [7, 3, 1];

    public function __construct(
        private readonly TrialExpirationReader $reader,
        private readonly TrialExpirationNotifier $notifier,
        private readonly PauseTenant $pauseTenant,
        private readonly OwnerSubscriptionsQuery $subscriptions,
    ) {}

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
        $cacheKey = "trial_reminder_{$daysLeft}d";
        $sent = 0;
        $failures = 0;

        foreach ($this->reader->tenantsRemindable($daysLeft) as $tenant) {
            $sentKey = "sent_{$cacheKey}_{$tenant->id}";
            if (cache()->has($sentKey)) {
                continue;
            }

            $user = $this->reader->userFor($tenant);
            if (! $user instanceof User) {
                continue;
            }
            // subscribed() is false for past_due, so a paying owner whose card failed would still be told to subscribe.
            if ($this->subscriptions->open($user)->isNotEmpty()) {
                continue;
            }

            if ($this->notifier->sendReminder($user, $tenant, $daysLeft)) {
                cache()->put($sentKey, true, now()->addDays(30));
                $sent++;
            } else {
                $failures++;
            }
        }

        return [$sent, $failures];
    }

    private function pauseExpired(): int
    {
        $pausings = 0;

        foreach ($this->reader->tenantsExpired() as $tenant) {
            $user = $this->reader->userFor($tenant);

            if ($user instanceof User && ($user->subscribed('default') || $this->subscriptions->inPastDueGrace($user))) {
                continue;
            }

            ($this->pauseTenant)($tenant);
            $pausings++;

            // Someone who paid before is not told their trial expired: Stripe's own emails cover a failed payment.
            if ($user instanceof User && ! $this->subscriptions->hasEverSubscribed($user)) {
                $this->notifier->notifyExpired($user, $tenant);
            }

            Log::info('Trial expired — bakery paused', ['tenant' => $tenant->id]);
        }

        return $pausings;
    }
}
