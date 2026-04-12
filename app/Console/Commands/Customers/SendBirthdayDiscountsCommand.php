<?php

namespace App\Console\Commands\Customers;

use App\Services\Engagement\EngagementDispatcher;
use App\Services\Engagement\Engagements\BirthdayDiscountEngagement;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('birthday:send-discounts')]
#[Description('Send birthday discount coupons to customers with birthdays today across all tenants')]
class SendBirthdayDiscountsCommand extends Command
{
    public function handle(EngagementDispatcher $dispatcher, BirthdayDiscountEngagement $engagement): int
    {
        $failures = $dispatcher->dispatch($engagement, $this);

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
