<?php

namespace App\Console\Commands\Customers;

use App\Services\Engagement\EngagementDispatcher;
use App\Services\Engagement\Engagements\RepeatOrderReminderEngagement;
use Illuminate\Console\Command;

class SendRepeatOrderRemindersCommand extends Command
{
    protected $signature = 'orders:send-repeat-reminders';

    protected $description = 'Send repeat order reminders to customers across all tenants';

    public function handle(EngagementDispatcher $dispatcher, RepeatOrderReminderEngagement $engagement): int
    {
        $failures = $dispatcher->dispatch($engagement, $this);

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
