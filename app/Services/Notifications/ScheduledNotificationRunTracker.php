<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\Operations\ScheduledNotificationRun;

class ScheduledNotificationRunTracker
{
    public function claim(string $notificationKey): bool
    {
        $now = now();

        return ScheduledNotificationRun::query()->insertOrIgnore([
            'notification_key' => $notificationKey,
            'claimed_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]) === 1;
    }

    public function release(string $notificationKey): void
    {
        ScheduledNotificationRun::query()
            ->where('notification_key', $notificationKey)
            ->delete();
    }
}
