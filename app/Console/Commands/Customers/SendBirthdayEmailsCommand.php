<?php

namespace App\Console\Commands\Customers;

use App\Services\Engagement\EngagementDispatcher;
use App\Services\Engagement\Engagements\BirthdayEmailEngagement;
use Illuminate\Console\Command;

class SendBirthdayEmailsCommand extends Command
{
    protected $signature = 'birthday:send-emails';

    protected $description = 'Send happy birthday emails to customers with birthdays today';

    public function handle(EngagementDispatcher $dispatcher, BirthdayEmailEngagement $engagement): int
    {
        $failures = $dispatcher->dispatch($engagement, $this);

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
