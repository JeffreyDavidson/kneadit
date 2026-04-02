<?php

namespace App\Console\Commands\Customers;

use App\Services\Engagement\EngagementDispatcher;
use App\Services\Engagement\Engagements\ReviewRequestEngagement;
use Illuminate\Console\Command;

class SendReviewRequestsCommand extends Command
{
    protected $signature = 'reviews:send-requests';

    protected $description = 'Send review request emails for recently delivered orders';

    public function handle(EngagementDispatcher $dispatcher, ReviewRequestEngagement $engagement): int
    {
        $failures = $dispatcher->dispatch($engagement, $this);

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
