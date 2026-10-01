<?php

declare(strict_types=1);

namespace App\Console\Commands\Customers;

use App\Services\Engagement\EngagementDispatcher;
use App\Services\Engagement\Engagements\RepeatOrderReminderEngagement;
use App\Services\Scheduling\LocalSendSchedule;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:send-repeat-reminders {--force : Run now, ignoring the bakery-local send hour}')]
#[Description('Send repeat order reminders to customers across all tenants')]
class SendRepeatOrderRemindersCommand extends Command
{
    /** Each bakery is processed when its own clock reads this hour. */
    private const int SEND_HOUR = 10;

    public function handle(EngagementDispatcher $dispatcher, RepeatOrderReminderEngagement $engagement): int
    {
        $failures = $dispatcher->dispatch(
            $engagement,
            $this,
            new LocalSendSchedule('orders:send-repeat-reminders', self::SEND_HOUR),
            $this->option('force') === true,
        );

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
