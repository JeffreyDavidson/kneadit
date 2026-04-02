<?php

namespace App\Console\Commands\Customers;

use App\Services\Engagement\EngagementDispatcher;
use App\Services\Engagement\Engagements\BirthdayDiscountEngagement;
use Illuminate\Console\Command;

class SendBirthdayDiscountsCommand extends Command
{
    protected $signature = 'birthday:send-discounts';

    protected $description = 'Send birthday discount coupons to customers with birthdays today across all tenants';

    public function handle(EngagementDispatcher $dispatcher, BirthdayDiscountEngagement $engagement): int
    {
        $failures = $dispatcher->dispatch($engagement, $this);

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
