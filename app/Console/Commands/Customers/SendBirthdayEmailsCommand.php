<?php

declare(strict_types=1);

namespace App\Console\Commands\Customers;

use App\Services\Engagement\EngagementDispatcher;
use App\Services\Engagement\Engagements\BirthdayEngagement;
use App\Services\Scheduling\LocalSendSchedule;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('birthday:send-emails {--force : Run now, ignoring the bakery-local send hour}')]
#[Description('Send happy birthday emails to customers with birthdays today')]
class SendBirthdayEmailsCommand extends Command
{
    /** Each bakery is processed when its own clock reads this hour. */
    private const int SEND_HOUR = 8;

    public function handle(EngagementDispatcher $dispatcher, BirthdayEngagement $engagement): int
    {
        $failures = $dispatcher->dispatch(
            $engagement,
            $this,
            new LocalSendSchedule('birthday:send-emails', self::SEND_HOUR),
            $this->option('force') === true,
        );

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
