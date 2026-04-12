<?php

namespace App\Console\Commands\Customers;

use App\Services\Engagement\EngagementDispatcher;
use App\Services\Engagement\Engagements\ReviewRequestEngagement;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reviews:send-requests')]
#[Description('Send review request emails for recently delivered orders')]
class SendReviewRequestsCommand extends Command
{
    public function handle(EngagementDispatcher $dispatcher, ReviewRequestEngagement $engagement): int
    {
        $failures = $dispatcher->dispatch($engagement, $this);

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
