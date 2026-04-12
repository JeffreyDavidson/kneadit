<?php

namespace App\Console\Commands\Customers;

use App\Services\Engagement\EngagementDispatcher;
use App\Services\Engagement\Engagements\RepeatOrderReminderEngagement;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:send-repeat-reminders')]
#[Description('Send repeat order reminders to customers across all tenants')]
class SendRepeatOrderRemindersCommand extends Command
{
    public function handle(EngagementDispatcher $dispatcher, RepeatOrderReminderEngagement $engagement): int
    {
        $failures = $dispatcher->dispatch($engagement, $this);

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
