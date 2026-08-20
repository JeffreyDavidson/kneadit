<?php

namespace App\Builders\Engagement;

use App\Enums\Marketing\CustomerCampaignStatus;
use App\Models\Engagement\CustomerCampaign;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<CustomerCampaign> */
class CustomerCampaignQueryBuilder extends Builder
{
    public function scheduled(): static
    {
        $this->where('status', CustomerCampaignStatus::Scheduled);

        return $this;
    }

    public function due(): static
    {
        $this->scheduled()
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now());

        return $this;
    }
}
