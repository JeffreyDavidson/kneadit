<?php

namespace App\Console\Commands;

use App\Mail\TrialExpiredMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckTrialExpirations extends Command
{
    protected $signature = 'trial:check';

    protected $description = 'Send trial expiration reminder emails and restrict expired accounts';

    public function handle(): int
    {
        $this->sendReminders(7, 'trial_reminder_7d');
        $this->sendReminders(3, 'trial_reminder_3d');
        $this->sendReminders(1, 'trial_reminder_1d');
        $this->handleExpired();

        return Command::SUCCESS;
    }

    protected function sendReminders(int $daysLeft, string $reminderKey): void
    {
        $targetDate = now()->addDays($daysLeft)->startOfDay();

        $tenants = Tenant::query()->whereDate('trial_ends_at', $targetDate->toDateString())
            ->where('is_active', true)
            ->cursor();

        /** @var Tenant $tenant */
        foreach ($tenants as $tenant) {
            // Check if we already sent this reminder
            $sentKey = "sent_{$reminderKey}_{$tenant->id}";
            if (cache()->has($sentKey)) {
                continue;
            }

            $user = User::query()->where('email', $tenant->email)->first();
            if (! $user) {
                continue;
            }

            // Skip if user has an active subscription (they converted during trial)
            if ($user->subscribed('default')) {
                continue;
            }

            try {
                $storeName = $tenant->store_name ?: $tenant->name;
                $daysText = $daysLeft === 1 ? 'tomorrow' : "in {$daysLeft} days";

                Mail::raw(
                    "Hi {$user->name},\n\n".
                    "Your KneadIt free trial for {$storeName} ends {$daysText}.\n\n".
                    "Subscribe now to keep your bakery running without interruption:\n".
                    "https://getkneadit.app/billing/plans\n\n".
                    ($daysLeft <= 3
                        ? "After your trial expires, your storefront will be paused until you subscribe.\n\n"
                        : '').
                    "Questions? Just reply to this email.\n\n— The KneadIt Team",
                    function (Message $m) use ($user, $daysLeft) {
                        $subjects = [
                            7 => 'Your KneadIt trial ends in 7 days',
                            3 => '⏰ 3 days left on your KneadIt trial',
                            1 => '🚨 Your KneadIt trial ends tomorrow',
                        ];
                        $m->to($user->email)
                            ->subject($subjects[$daysLeft] ?? 'Trial ending soon')
                            ->from(config('mail.from.address'), 'KneadIt');
                    }
                );

                cache()->put($sentKey, true, now()->addDays(30));
                $this->info("  ✓ {$daysLeft}d reminder → {$user->email} ({$tenant->store_name})");
            } catch (\Exception $e) {
                Log::error('Trial check failed', ['email' => $user->email, 'error' => $e->getMessage()]);
                $this->error("  ✗ Failed: {$user->email} — {$e->getMessage()}");
            }
        }
    }

    protected function handleExpired(): void
    {
        // Find tenants whose trial has expired and have no active subscription
        $expiredTenants = Tenant::query()->where('trial_ends_at', '<', now())
            ->where('is_active', true)
            ->cursor();

        /** @var Tenant $tenant */
        foreach ($expiredTenants as $tenant) {
            $user = User::query()->where('email', $tenant->email)->first();

            // Skip if they have an active subscription
            if ($user && $user->subscribed('default')) {
                continue;
            }

            // Deactivate storefront (admin still accessible so they can subscribe)
            if ($tenant->storefront_enabled) {
                $tenant->update(['storefront_enabled' => false]);

                $this->info("  ⏸ Paused storefront: {$tenant->store_name} ({$tenant->id})");

                // Send expiration email
                if ($user) {
                    try {
                        Mail::to($user->email)->queue(new TrialExpiredMail($user, $tenant->id));
                    } catch (\Exception $e) {
                        Log::error("Trial expiration email failed for {$tenant->id}", ['error' => $e->getMessage()]);
                    }
                }

                Log::info('Trial expired — storefront paused', ['tenant' => $tenant->id]);
            }
        }
    }
}
