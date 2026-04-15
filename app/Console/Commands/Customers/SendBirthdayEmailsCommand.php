<?php

namespace App\Console\Commands\Customers;

use App\Services\Engagement\EngagementDispatcher;
use App\Services\Engagement\Engagements\BirthdayEngagement;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('birthday:send-emails')]
#[Description('Send happy birthday emails to customers with birthdays today')]
class SendBirthdayEmailsCommand extends Command
{
    public function handle(EngagementDispatcher $dispatcher, BirthdayEngagement $engagement): int
    {
        $failures = $dispatcher->dispatch($engagement, $this);

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
